<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with('category')->latest()->get();
        // Calculate totals for summary cards
        $totalItems = $items->count();
        $lowStockItems = $items->where('is_low_stock', true)->count();
        $totalValue = $items->sum(fn($item) => $item->current_stock * $item->cost_per_unit);

        return view('inventory.index', compact('items', 'totalItems', 'lowStockItems', 'totalValue'));
    }

    public function create()
    {
        $categories = InventoryCategory::all();
        return view('inventory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'sku' => 'nullable|string|unique:inventory_items,sku',
            'unit' => 'required|string',
            'current_stock' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated) {
            $item = InventoryItem::create($validated);

            // Record initial stock transaction
            if ($item->current_stock > 0) {
                InventoryTransaction::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'type' => 'initial',
                    'quantity' => $item->current_stock,
                    'notes' => 'رصيد افتتاحي عند الإنشاء',
                ]);
            }
        });

        return redirect()->route('inventory.index')->with('success', 'تم إضافة الصنف بنجاح');
    }







    // public function edit(InventoryItem $item)
    // {
    //     $categories = InventoryCategory::all();
    //     return view('inventory.edit', compact('item', 'categories'));
    // }

    // public function update(Request $request, InventoryItem $item)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'category_id' => 'nullable|exists:inventory_categories,id',
    //         'sku' => 'nullable|string|unique:inventory_items,sku,' . $item->id,
    //         'unit' => 'required|string',
    //         'min_stock_level' => 'required|integer|min:0',
    //         'cost_per_unit' => 'required|numeric|min:0',
    //         'expiry_date' => 'nullable|date',
    //     ]);

    //     $item->update($validated);

    //     return redirect()->route('inventory.index')->with('success', 'تم تحديث بيانات الصنف');
    // }




    public function edit(InventoryItem $inventory)
{
    $categories = InventoryCategory::all();
    return view('inventory.edit', [
        'item' => $inventory,
        'categories' => $categories
    ]);
}

public function update(Request $request, InventoryItem $inventory)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:inventory_categories,id',
        'sku' => 'nullable|string|unique:inventory_items,sku,' . $inventory->id,
        'unit' => 'required|string',
        'min_stock_level' => 'required|integer|min:0',
        'cost_per_unit' => 'required|numeric|min:0',
        'expiry_date' => 'nullable|date',
    ]);

    $inventory->update($validated);

    return redirect()->route('inventory.index')->with('success', 'تم تحديث بيانات الصنف');
}




    
    public function destroy(InventoryItem $inventory)
    {
        // Remove related transactions to avoid orphaned records affecting calculations
        \DB::transaction(function () use ($inventory) {
            \App\Models\InventoryTransaction::where('item_id', $inventory->id)->delete();
            $inventory->delete();
        });

        return redirect()->route('inventory.index')->with('success', 'تم حذف الصنف');
    }

    // Add stock (Purchase) or Remove stock (Consume)
    public function adjustment(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'type' => 'required|in:add,subtract',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $quantity = $validated['quantity'];
        
        DB::transaction(function () use ($item, $validated, $quantity) {
            if ($validated['type'] === 'add') {
                $item->increment('current_stock', $quantity);
                $type = 'purchase';
            } else {
                if ($item->current_stock < $quantity) {
                    abort(422, 'الكمية غير متوفرة في المخزون');
                }
                $item->decrement('current_stock', $quantity);
                $type = 'consumption';
            }

            InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $validated['type'] === 'add' ? $quantity : -$quantity,
                'notes' => $validated['notes'],
            ]);
        });

        return back()->with('success', 'تم تحديث المخزون بنجاح');
    }
}
