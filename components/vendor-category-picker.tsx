'use client'

import { useState } from 'react'

type Category = { id: string; name: string; slug: string }

export function VendorCategoryPicker({
  categories,
  id = 'category_id',
  initialCategoryId = '',
  variant = 'onboarding',
}: {
  categories: Category[]
  id?: string
  initialCategoryId?: string
  variant?: 'onboarding' | 'workspace'
}) {
  const [categoryId, setCategoryId] = useState(initialCategoryId)
  const selected = categories.find((category) => category.id === categoryId)
  const isOther = selected?.slug === 'other-campus-services'

  return (
    <>
      <select
        id={id}
        name="category_id"
        required
        value={categoryId}
        onChange={(event) => setCategoryId(event.target.value)}
      >
        <option value="" disabled>{variant === 'onboarding' ? 'Select category' : 'Choose category'}</option>
        {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
      </select>
      {isOther ? (
        <div className={variant === 'onboarding' ? 'field-block' : 'vendor-other-service-field'} style={{ marginTop: 12 }}>
          <label htmlFor={id + '_other_service_details'}>Specify your other campus service</label>
          <input
            id={id + '_other_service_details'}
            name="other_service_details"
            required
            minLength={3}
            maxLength={120}
            placeholder="e.g. Website development, shoe restoration, event decoration"
            autoComplete="off"
          />
          <small>Describe the service missing from our list. CampusMode Admins can review it; submitting does not automatically create a new category.</small>
        </div>
      ) : null}
    </>
  )
}
