<?php
namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\{InventoryItem,MenuCategory,MenuItem,MenuModifier,Outlet,Recipe};
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $r)
    {
        $pid = $r->user()->property_id;
        $outlets = Outlet::where('property_id',$pid)->orderBy('name')->get();
        $categories = MenuCategory::where('property_id',$pid)->with('outlet')->orderBy('display_order')->get();
        $items = MenuItem::with(['category','outlet','inventoryItem','modifiers','recipe.ingredients.inventoryItem'])
            ->where('property_id',$pid)->orderBy('name')->get();
        $inventory = InventoryItem::where('property_id',$pid)->where('is_active',true)->orderBy('name')->get();
        $modifiers = MenuModifier::where('property_id',$pid)->where('is_active',true)->orderBy('name')->get();
        return view('pos.menu',compact('outlets','categories','items','inventory','modifiers'));
    }

    public function category(Request $r)
    {
        $d=$r->validate(['outlet_id'=>'nullable|integer','name'=>'required|max:120','display_order'=>'nullable|integer|min:0']);
        MenuCategory::create(['property_id'=>$r->user()->property_id]+$d);
        return back()->with('success','Menu category created.');
    }

    public function item(Request $r)
    {
        $d=$r->validate([
            'outlet_id'=>'nullable|integer','menu_category_id'=>'nullable|integer','inventory_item_id'=>'nullable|integer',
            'sku'=>'nullable|max:50','name'=>'required|max:150','description'=>'nullable|max:1000','price'=>'required|numeric|min:0',
            'tax_rate'=>'nullable|numeric|min:0|max:100','service_charge_rate'=>'nullable|numeric|min:0|max:100',
            'stock_quantity_per_sale'=>'nullable|numeric|min:0','send_to_kitchen'=>'nullable|boolean','is_bar_item'=>'nullable|boolean',
            'modifier_ids'=>'array','modifier_ids.*'=>'integer'
        ]);
        $modifierIds=$d['modifier_ids']??[];
        unset($d['modifier_ids']);
        $d['property_id']=$r->user()->property_id;
        $d['tax_rate']=$d['tax_rate']??0;
        $d['service_charge_rate']=$d['service_charge_rate']??0;
        $d['stock_quantity_per_sale']=$d['stock_quantity_per_sale']??0;
        $d['send_to_kitchen']=$r->boolean('send_to_kitchen');
        $d['is_bar_item']=$r->boolean('is_bar_item');
        $item=MenuItem::create($d);
        $item->modifiers()->sync(MenuModifier::where('property_id',$r->user()->property_id)->whereIn('id',$modifierIds)->pluck('id'));
        return back()->with('success','Menu item created.');
    }

    public function modifier(Request $r)
    {
        $d=$r->validate(['name'=>'required|max:120','price_delta'=>'nullable|numeric']);
        MenuModifier::create(['property_id'=>$r->user()->property_id,'name'=>$d['name'],'price_delta'=>$d['price_delta']??0,'is_active'=>true]);
        return back()->with('success','Modifier created.');
    }

    public function recipe(Request $r, MenuItem $item)
    {
        abort_unless($item->property_id===$r->user()->property_id,404);
        $d=$r->validate([
            'name'=>'required|max:150','yield_quantity'=>'required|numeric|min:0.0001','ingredients'=>'required|array|min:1',
            'ingredients.*.inventory_item_id'=>'required|integer','ingredients.*.quantity'=>'required|numeric|min:0.0001'
        ]);
        $recipe=Recipe::updateOrCreate(['menu_item_id'=>$item->id],[
            'property_id'=>$item->property_id,'name'=>$d['name'],'yield_quantity'=>$d['yield_quantity']
        ]);
        $recipe->ingredients()->delete();
        foreach($d['ingredients'] as $ingredient){
            $stock=InventoryItem::where('property_id',$item->property_id)->findOrFail($ingredient['inventory_item_id']);
            $recipe->ingredients()->create(['inventory_item_id'=>$stock->id,'quantity'=>$ingredient['quantity']]);
        }
        return back()->with('success','Recipe saved.');
    }
}
