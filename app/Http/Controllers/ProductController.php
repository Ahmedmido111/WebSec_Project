<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        if (!auth()->user() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))) {
            abort(403, 'Unauthorized');
        }
        $products = Product::paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        if (!auth()->user() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))) {
            abort(403, 'Unauthorized');
        }
        return view('products.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        // Determine the route prefix based on the user's role
        $routePrefix = '';
        if (request()->route()->getPrefix() === 'admin') {
            $routePrefix = 'admin.';
        } elseif (request()->route()->getPrefix() === 'manager') {
            $routePrefix = 'manager.';
        }
        
        return redirect()->route($routePrefix . 'products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        // Redirect to the shop-details view which already exists
        return redirect()->route('shop.details', $product->id);
    }

    public function edit(Product $product)
    {
        if (!auth()->user() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))) {
            abort(403, 'Unauthorized');
        }
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        if (!auth()->user() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        // Determine the route prefix based on the route prefix
        $routePrefix = '';
        if (request()->route()->getPrefix() === 'admin') {
            $routePrefix = 'admin.';
        } elseif (request()->route()->getPrefix() === 'manager') {
            $routePrefix = 'manager.';
        }
        
        return redirect()->route($routePrefix . 'products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (!auth()->user() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))) {
            abort(403, 'Unauthorized');
        }
        $product->delete();

        // Determine the route prefix based on the route prefix
        $routePrefix = '';
        if (request()->route()->getPrefix() === 'admin') {
            $routePrefix = 'admin.';
        } elseif (request()->route()->getPrefix() === 'manager') {
            $routePrefix = 'manager.';
        }
        
        return redirect()->route($routePrefix . 'products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function inventoryReport(Request $request)
    {
        // Apply filters
        $query = Product::query();
        
        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Remove category filter since the column doesn't exist
        
        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'out':
                    $query->where('stock', '<=', 0);
                    break;
                case 'low':
                    $query->whereBetween('stock', [1, 9]);
                    break;
                case 'normal':
                    $query->where('stock', '>=', 10);
                    break;
            }
        }
        
        $products = $query->get();
        
        // Use a fixed list of categories instead of querying the database
        $categories = ['Electronics', 'Clothing', 'Food', 'Other'];
        
        // Calculate category-based data for charts
        // We'll use a default category for all products since the column doesn't exist
        $categoryData = [
            'Electronics' => [
                'stock' => $products->sum('stock'),
                'value' => $products->sum(function($product) {
                    return $product->price * $product->stock;
                })
            ]
        ];
        
        // Calculate stock status distribution
        $stockStatus = [
            'outOfStock' => $products->where('stock', '<=', 0)->count(),
            'critical' => $products->where('stock', '>', 0)->where('stock', '<', 5)->count(),
            'low' => $products->where('stock', '>=', 5)->where('stock', '<', 10)->count(),
            'normal' => $products->where('stock', '>=', 10)->count()
        ];
        
        return view('manager.reports.inventory', compact('products', 'categories', 'categoryData', 'stockStatus'));
    }

    public function inventoryReportPdf()
    {
        // Recommend installing DomPDF package first if not already installed:
        // composer require barryvdh/laravel-dompdf
        
        $products = Product::all();
        
        // If using barryvdh/laravel-dompdf package:
        // return PDF::loadView('pdfs.inventory_report', compact('products'))
        //     ->setPaper('a4', 'landscape')
        //     ->download('inventory_report_' . date('Y-m-d') . '.pdf');
        
        // Since we don't have the PDF package installed yet, we'll return a simplified HTML version
        // that can be printed to PDF from the browser
        return view('pdfs.inventory_report', compact('products'));
    }

    public function shop(Request $request)
    {
        $products = Product::paginate(12);
        foreach ($products as $product) {
            $product->image_url = $product->image_url;
        }
        // Use dummy categories and brands if models do not exist
        $categories = [
            (object)['id' => 1, 'name' => 'Electronics', 'slug' => 'electronics'],
            (object)['id' => 2, 'name' => 'Fashion', 'slug' => 'fashion'],
            (object)['id' => 3, 'name' => 'Home & Living', 'slug' => 'home'],
        ];
        $brands = [
            (object)['id' => 1, 'name' => 'Brand A'],
            (object)['id' => 2, 'name' => 'Brand B'],
            (object)['id' => 3, 'name' => 'Brand C'],
        ];
        return view('webfront.shop', compact('products', 'categories', 'brands'));
    }

    public function showDetails($id)
    {
        $product = Product::findOrFail($id);
        $product->image_url = $product->image_url;
        $relatedProducts = Product::where('id', '!=', $id)->take(4)->get();
        foreach ($relatedProducts as $relatedProduct) {
            $relatedProduct->image_url = $relatedProduct->image_url;
        }
        return view('webfront.shop-details', compact('product', 'relatedProducts'));
    }

    public function staffInventory(Request $request)
    {
        $query = Product::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Stock filter
        if ($request->has('stock')) {
            switch ($request->stock) {
                case 'low':
                    $query->whereBetween('stock', [1, 5]);
                    break;
                case 'out':
                    $query->where('stock', 0);
                    break;
                case 'in':
                    $query->where('stock', '>', 5);
                    break;
            }
        }

        $products = $query->latest()->paginate(10);
        return view('staff.inventory.index', compact('products'));
    }
} 