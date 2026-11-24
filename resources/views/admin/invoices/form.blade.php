@extends('layouts.admin')
@section('title', 'Invoice')
@section('content')
<h1 class="text-2xl font-bold">{{ $invoice->exists ? 'Edit draft '.$invoice->number : 'Prepare invoice' }} <span class="text-base font-normal text-slate-500">for {{ $req->reference }} · {{ $req->project_title }}</span></h1>
<p class="mt-1 text-sm text-slate-600">Direct-service invoices are priced manually from the reviewed scope ({{ $req->headcount }} {{ $req->personnel_type }}, {{ implode(', ', $req->project_countries) }}). Nothing is charged automatically.</p>
<form method="POST" action="{{ $invoice->exists ? route('admin.invoices.update', $invoice) : route('admin.invoices.store', $req) }}" class="mt-4 max-w-4xl space-y-4 rounded-xl bg-white p-6"
  x-data="{ items: @js(array_values(old('items', $items))), tax: {{ (float) old('tax_rate', $invoice->tax_rate ?? 0) }},
    get sub() { return this.items.reduce((s, i) => s + (parseFloat(i.quantity) || 0) * (parseFloat(i.unit_price) || 0), 0) } }">
@csrf @if($invoice->exists) @method('PUT') @endif
<div class="grid gap-4 sm:grid-cols-2"><x-field name="bill_to_name" label="Bill to" :value="$invoice->bill_to_name" required/><x-field name="bill_to_email" label="Send to (nominated contact)" type="email" :value="$invoice->bill_to_email" required/>
<x-field name="currency" label="Currency" maxlength="3" :value="$invoice->currency" required/><x-field name="due_date" label="Due date (default: +{{ config('afren.invoice_due_days') }} days)" type="date" :value="$invoice->due_date?->toDateString()"/></div>
<div><h2 class="font-semibold">Line items</h2>
<template x-for="(it, idx) in items" :key="idx"><div class="mt-2 grid grid-cols-12 gap-2">
<input class="col-span-6 rounded border-slate-300 text-sm" :name="`items[${idx}][description]`" x-model="it.description" placeholder="Description (e.g. Recruitment of 10 enumerators, Kenya)" required>
<input class="col-span-2 rounded border-slate-300 text-sm" type="number" step="0.01" min="0.01" :name="`items[${idx}][quantity]`" x-model="it.quantity" placeholder="Qty" required>
<input class="col-span-3 rounded border-slate-300 text-sm" type="number" step="0.01" min="0" :name="`items[${idx}][unit_price]`" x-model="it.unit_price" placeholder="Unit price" required>
<button type="button" class="col-span-1 text-red-600" @click="items.splice(idx, 1)" x-show="items.length > 1" aria-label="Remove line">✕</button></div></template>
<button type="button" class="mt-2 text-sm text-brand-700 underline" @click="items.push({description: '', quantity: 1, unit_price: ''})">+ Add line</button></div>
<div class="grid gap-4 sm:grid-cols-3"><x-field name="tax_rate" label="Tax rate % (pre-filled from tax rules)" type="number" step="0.01" min="0" max="100" :value="$invoice->tax_rate ?? 0" x-model.number="tax"/>
<p class="self-end text-sm">Subtotal: <b x-text="sub.toFixed(2)"></b> · Tax: <b x-text="(sub * tax / 100).toFixed(2)"></b> · Total: <b x-text="(sub * (1 + tax / 100)).toFixed(2)"></b></p></div>
<x-textarea name="notes" label="Notes / payment terms" :value="$invoice->notes"/>
<button class="rounded bg-brand-700 px-5 py-2 text-white">Save draft</button></form>
@endsection
