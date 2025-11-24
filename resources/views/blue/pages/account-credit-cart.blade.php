@extends('blue.layouts.page')

@php
$title = 'Account Credit Checkout';
@endphp

@section('title', $title)

@section('head')
@parent
<meta name="description" content="" />
@endsection

@section('page-content')

<section class="inner-page-title">
    <div class="layout-box">
        <h1 style="text-align: center">Shopping Cart</h1>
    </div>
</section>


<qrcg-account-credit-cart-view></qrcg-account-credit-cart-view>

@endsection