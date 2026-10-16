@extends('layouts.app')

@section('content')
<h1>TechShop Monolith (Laravel)</h1>
<p class="muted">Du an moi da duoc to chuc lai thanh mot codebase Laravel duy nhat.</p>

<div class="grid">
    @forelse($featuredProducts as $product)
    <article class="card">
        <h3>{{ $product->name }}</h3>
        <p class="muted">SKU: #{{ $product->id }}</p>
        <p>
            @if(!empty($product->sale_price) && $product->sale_price > 0)
            <strong>{{ number_format($product->sale_price, 0, ',', '.') }} d</strong>
            <span class="muted"><s>{{ number_format($product->price, 0, ',', '.') }} d</s></span>
            @else
            <strong>{{ number_format($product->price, 0, ',', '.') }} d</strong>
            @endif
        </p>
    </article>
    @empty
    <article class="card">Khong co san pham nao.</article>
    @endforelse
</div>
@endsection