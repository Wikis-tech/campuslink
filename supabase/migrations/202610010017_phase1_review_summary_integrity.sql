-- Phase 1 data correctness: keep vendor review summary fields synchronized
-- with the authoritative published reviews table.

create or replace function private.refresh_vendor_review_summary(target_vendor uuid)
returns void
language plpgsql
security definer
set search_path to ''
as $function$
begin
  if target_vendor is null then
    return;
  end if;

  update public.vendor_profiles vp
  set average_rating = coalesce((
        select round(avg(r.rating)::numeric, 2)
        from public.reviews r
        where r.vendor_id = target_vendor
          and r.status = 'published'
      ), 0),
      review_count = (
        select count(*)::integer
        from public.reviews r
        where r.vendor_id = target_vendor
          and r.status = 'published'
      )
  where vp.id = target_vendor;
end;
$function$;

create or replace function private.sync_vendor_review_summary_trigger()
returns trigger
language plpgsql
security definer
set search_path to ''
as $function$
begin
  if tg_op = 'DELETE' then
    perform private.refresh_vendor_review_summary(old.vendor_id);
    return old;
  end if;

  perform private.refresh_vendor_review_summary(new.vendor_id);

  if tg_op = 'UPDATE' and old.vendor_id is distinct from new.vendor_id then
    perform private.refresh_vendor_review_summary(old.vendor_id);
  end if;

  return new;
end;
$function$;

drop trigger if exists reviews_sync_vendor_summary on public.reviews;
create trigger reviews_sync_vendor_summary
after insert or update or delete on public.reviews
for each row execute function private.sync_vendor_review_summary_trigger();

update public.vendor_profiles vp
set average_rating = coalesce((
      select round(avg(r.rating)::numeric, 2)
      from public.reviews r
      where r.vendor_id = vp.id
        and r.status = 'published'
    ), 0),
    review_count = (
      select count(*)::integer
      from public.reviews r
      where r.vendor_id = vp.id
        and r.status = 'published'
    );
