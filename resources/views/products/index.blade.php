@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Products') }}</h5>
                    <a href="{{ auth()->user()->hasRole('admin') ? route('admin.products.create') : route('products.create') }}" class="btn btn-primary">
                        {{ __('Add New Product') }}
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Stock') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-width: 50px;">
                                        </td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ ucfirst($product->category) }}</td>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if(auth()->user()->hasRole('admin'))
                                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info btn-sm">
                                                        {{ __('View') }}
                                                    </a>
                                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-sm">
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to delete this product?') }}')">
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-info btn-sm">
                                                        {{ __('View') }}
                                                    </a>
                                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm">
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to delete this product?') }}')">
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No products found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($products->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 