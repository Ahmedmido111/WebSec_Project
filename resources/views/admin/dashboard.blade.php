@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Admin Dashboard</h2>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Quick Stats -->
                        <div class="col-md-3 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Users</h5>
                                    <h2 class="card-text">{{ \App\Models\User::count() }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Products</h5>
                                    <h2 class="card-text">{{ \App\Models\Product::count() }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Orders</h5>
                                    <h2 class="card-text">{{ \App\Models\Order::count() }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Roles</h5>
                                    <h2 class="card-text">{{ \App\Models\Role::count() }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h3>Quick Actions</h3>
                            <div class="list-group">
                                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users"></i> Manage Users
                                </a>
                                <a href="{{ route('roles.index') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-tag"></i> Manage Roles
                                </a>
                                <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-box"></i> Manage Products
                                </a>
                                <a href="{{ route('settings') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cog"></i> System Settings
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h3>Recent Activity</h3>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Admin User</td>
                                            <td>Logged in</td>
                                            <td>{{ now() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .list-group-item {
        transition: background-color 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush 