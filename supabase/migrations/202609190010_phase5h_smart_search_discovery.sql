create extension if not exists pg_trgm with schema extensions;

create or replace function public.student_smart_search(
  target_institution uuid,
  search_query text default '',
  category_slug text default null,
  result_limit integer default 80
)
returns table (
  entity_type text,
  entity_id uuid,
  vendor_id uuid,
  relevance_score numeric,
  match_reason text
)
language sql
stable
security invoker
set search_path = public, extensions
as $$
with me as (
  select p.institution_id
  from public.profiles p
  where p.id = auth.uid()
    and p.account_type = 'student'
    and p.institution_id = target_institution
    and p.onboarding_completed_at is not null
),
eligible_vendors as (
  select vp.*
  from public.vendor_profiles vp
  join public.vendor_institutions vi
    on vi.vendor_id = vp.id
   and vi.institution_id = target_institution
   and vi.status = 'approved'
  where exists (select 1 from me)
    and vp.verification_status = 'approved'
    and (
      vp.marketplace_status = 'active'
      or (
        vp.marketplace_status = 'suspended'
        and vp.suspended_until is not null
        and vp.suspended_until <= now()
      )
    )
),
entities as (
  select 'vendor'::text entity_type, v.id entity_id, v.id vendor_id,
    v.business_name title, coalesce(v.description,'') description,
    null::uuid category_id, null::text category_name, null::text category_slug_value,
    v.business_name vendor_name, v.location_text, v.average_rating, v.review_count, v.created_at
  from eligible_vendors v
  union all
  select 'product', p.id, p.vendor_id, p.name, coalesce(p.description,''),
    p.category_id, c.name, c.slug, v.business_name, v.location_text,
    v.average_rating, v.review_count, p.created_at
  from public.vendor_products p
  join eligible_vendors v on v.id = p.vendor_id
  left join public.categories c on c.id = p.category_id
  where p.is_active = true
  union all
  select 'service', s.id, s.vendor_id, s.name, coalesce(s.description,''),
    s.category_id, c.name, c.slug, v.business_name, v.location_text,
    v.average_rating, v.review_count, s.created_at
  from public.vendor_services s
  join eligible_vendors v on v.id = s.vendor_id
  left join public.categories c on c.id = s.category_id
  where s.is_active = true
),
prepared as (
  select e.*,
    lower(trim(coalesce(search_query,''))) q,
    lower(concat_ws(' ', e.title, e.description, e.category_name, e.vendor_name, e.location_text)) searchable
  from entities e
  where category_slug is null or category_slug = ''
     or e.entity_type = 'vendor'
     or e.category_slug_value = category_slug
),
scored as (
  select p.*,
    case when p.q = '' then
      coalesce(p.average_rating,0)::numeric * 4
      + ln(coalesce(p.review_count,0) + 1)::numeric * 3
      + case when p.entity_type in ('product','service') then 3 else 0 end
      + greatest(0, 4 - extract(epoch from (now() - p.created_at)) / 2592000)::numeric
    else
      case when lower(p.title) = p.q then 120 else 0 end
      + case when lower(p.title) like p.q || '%' then 75 else 0 end
      + case when lower(p.title) like '%' || p.q || '%' then 48 else 0 end
      + case when lower(p.vendor_name) = p.q then 90 else 0 end
      + case when lower(p.vendor_name) like p.q || '%' then 52 else 0 end
      + case when lower(coalesce(p.category_name,'')) = p.q then 65 else 0 end
      + case when p.searchable like '%' || p.q || '%' then 28 else 0 end
      + extensions.similarity(lower(p.title), p.q) * 55
      + extensions.similarity(p.searchable, p.q) * 24
      + case when to_tsvector('simple', p.searchable) @@ websearch_to_tsquery('simple', p.q)
          then ts_rank_cd(to_tsvector('simple', p.searchable), websearch_to_tsquery('simple', p.q)) * 80 else 0 end
      + coalesce(p.average_rating,0)::numeric * 2
      + ln(coalesce(p.review_count,0) + 1)::numeric * 1.5
      + case when p.entity_type in ('product','service') then 4 else 0 end
    end score
  from prepared p
),
matched as (
  select * from scored s
  where s.q = ''
     or lower(s.title) like '%' || s.q || '%'
     or lower(s.vendor_name) like '%' || s.q || '%'
     or lower(coalesce(s.category_name,'')) like '%' || s.q || '%'
     or s.searchable like '%' || s.q || '%'
     or extensions.similarity(lower(s.title), s.q) >= 0.20
     or extensions.similarity(s.searchable, s.q) >= 0.12
     or to_tsvector('simple', s.searchable) @@ websearch_to_tsquery('simple', s.q)
)
select m.entity_type, m.entity_id, m.vendor_id, round(m.score::numeric,3),
  case
    when m.q = '' then 'Recommended for your campus'
    when lower(m.title) = m.q then 'Exact match'
    when lower(m.title) like m.q || '%' then 'Strong title match'
    when lower(m.vendor_name) = m.q then 'Vendor match'
    when lower(coalesce(m.category_name,'')) = m.q then 'Category match'
    when to_tsvector('simple', m.searchable) @@ websearch_to_tsquery('simple', m.q) then 'Relevant keywords'
    else 'Similar match'
  end
from matched m
order by m.score desc, m.average_rating desc nulls last, m.review_count desc nulls last, m.created_at desc
limit greatest(1, least(coalesce(result_limit,80),120));
$$;

revoke all on function public.student_smart_search(uuid,text,text,integer) from public;
grant execute on function public.student_smart_search(uuid,text,text,integer) to authenticated;

comment on function public.student_smart_search(uuid,text,text,integer) is
'Phase 5H campus-scoped smart discovery ranking. No paid-plan or subscription signal is used in relevance scoring.';
