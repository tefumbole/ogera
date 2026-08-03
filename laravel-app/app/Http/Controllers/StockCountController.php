<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Warehouse;
use App\Brand;
use App\Category;
use App\Product;
use DB;
use App\StockCount;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StockCountController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if( $role->hasPermissionTo('stock_count') ) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_brand_list = Brand::where('is_active', true)->get();
            $lims_category_list = Category::where('is_active', true)->get();
            $general_setting = DB::table('general_settings')->latest()->first();
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $lims_stock_count_all = StockCount::orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_stock_count_all = StockCount::orderBy('id', 'desc')->get();

            return view('stock_count.index', compact('lims_warehouse_list', 'lims_brand_list', 'lims_category_list', 'lims_stock_count_all'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if( isset($data['brand_id']) && isset($data['category_id']) ){
            $lims_product_list = DB::table('products')->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')->whereIn('products.category_id', $data['category_id'] )->whereIn('products.brand_id', $data['brand_id'] )->where([ ['products.is_active', true], ['product_warehouse.warehouse_id', $data['warehouse_id']] ])->select('products.name', 'products.code', 'product_warehouse.qty')->get();

            $data['category_id'] = implode(",", $data['category_id']);
            $data['brand_id'] = implode(",", $data['brand_id']);
        }
        elseif( isset($data['category_id']) ){
            $lims_product_list = DB::table('products')->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')->whereIn('products.category_id', $data['category_id'])->where([ ['products.is_active', true], ['product_warehouse.warehouse_id', $data['warehouse_id']] ])->select('products.name', 'products.code', 'product_warehouse.qty')->get();

            $data['category_id'] = implode(",", $data['category_id']);
        }
        elseif( isset($data['brand_id']) ){
            $lims_product_list = DB::table('products')->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')->whereIn('products.brand_id', $data['brand_id'])->where([ ['products.is_active', true], ['product_warehouse.warehouse_id', $data['warehouse_id']] ])->select('products.name', 'products.code', 'product_warehouse.qty')->get();

            $data['brand_id'] = implode(",", $data['brand_id']);
        }
        else{
            $lims_product_list = DB::table('products')->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')->where([ ['products.is_active', true], ['product_warehouse.warehouse_id', $data['warehouse_id']] ])->select('products.name', 'products.code', 'product_warehouse.qty')->get();
        }
        if( count($lims_product_list) ){
            // Build each row as an array and let fputcsv quote it. The old
            // code joined the fields with commas then split on commas again,
            // which shredded any product name containing a comma into extra
            // columns — and the re-upload then read the wrong column as the
            // product code.
            $csvData = [['Product Name', 'Product Code', 'Expected', 'Counted']];
            foreach ($lims_product_list as $product) {
                $csvData[] = [$product->name, $product->code, $product->qty, ''];
            }
            $filename= date('Ymd').'-'.date('his'). ".csv";
            $file_path= $this->stockCountPath($filename);
            $file = @fopen($file_path, "w+");
            if($file === false){
                return redirect()->back()->with('not_permitted', 'Could not write the stock count file. Please check that public/stock_count is writable.');
            }
            foreach ($csvData as $row){
              fputcsv($file, $row);
            }
            fclose($file);

            $data['user_id'] = Auth::id();
            $data['reference_no'] = 'scr-' . date("Ymd") . '-'. date("his");
            $data['initial_file'] = $filename;
            $data['is_adjusted'] = false;
            StockCount::create($data);
            return redirect()->back()->with('message', 'Stock Count created successfully! Please download the initial file to complete it.');
        }
        else
            return redirect()->back()->with('not_permitted', 'No product found!');
    }

    /**
     * Absolute path inside public/stock_count, creating the directory the
     * first time it is needed. The folder is gitignored, so a fresh checkout
     * or deploy would otherwise have no place to write the CSV.
     */
    protected function stockCountPath($filename = '')
    {
        $dir = public_path('stock_count');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $filename === '' ? $dir : $dir.'/'.$filename;
    }

    public function finalize(Request $request)
    {
        $ext = pathinfo($request->final_file->getClientOriginalName(), PATHINFO_EXTENSION);
        //checking if this is a CSV file
        if($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Please upload a CSV file');

        $data = $request->all();
        $document = $request->final_file;
        $documentName = date('Ymd').'-'.date('his'). ".csv";
        $document->move($this->stockCountPath(), $documentName);
        $data['final_file'] = $documentName;
        $lims_stock_count_data = StockCount::find($data['stock_count_id']);
        $lims_stock_count_data->update($data);
        return redirect()->back()->with('message', 'Stock Count finalized successfully!');
    }

    public function stockDif($id)
    {
        $lims_stock_count_data = StockCount::find($id);
        if(! $lims_stock_count_data || ! $lims_stock_count_data->final_file){
            return [];
        }

        $file_path = $this->stockCountPath($lims_stock_count_data->final_file);
        $file_handle = is_file($file_path) ? @fopen($file_path, 'r') : false;
        if($file_handle === false){
            return [];
        }

        $i = 0;
        $temp_dif = -1000000;
        $data = [];
        $product = [];
        $expected = [];
        $counted = [];
        $difference = [];
        $cost = [];
        while( !feof($file_handle) ) {
            $current_line = fgetcsv($file_handle);
            // Rows edited outside the app can be short; skip anything that
            // does not carry both an expected and a counted column.
            if( $current_line && $i > 0 && count($current_line) >= 4 && ($current_line[2] != $current_line[3]) ){
                $product[] = $current_line[0].' ['.$current_line[1].']';
                $expected[] = $current_line[2];
                $product_data = Product::where('code', $current_line[1])->first();

                if($current_line[3]){
                    $difference[] = $temp_dif = $current_line[3] - $current_line[2];
                    $counted[] = $current_line[3];
                }
                else{
                    $difference[] = $temp_dif = $current_line[2] * (-1);
                    $counted[] = 0;
                }
                // A code in the CSV may no longer exist as a product.
                $cost[] = $product_data ? $product_data->cost * $temp_dif : 0;
            }
            $i++;
        }
        fclose($file_handle);

        if($temp_dif == -1000000){
            $lims_stock_count_data->is_adjusted = true;
            $lims_stock_count_data->save();
        }
        if( count($product) ) {
            $data[] = $product;
            $data[] = $expected;
            $data[] = $counted;
            $data[] = $difference;
            $data[] = $cost;
            $data[] = $lims_stock_count_data->is_adjusted;
        }
        return $data;
    }

    public function qtyAdjustment($id)
    {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_stock_count_data = StockCount::find($id);
        if(! $lims_stock_count_data){
            return redirect()->back()->with('not_permitted', 'Stock count not found.');
        }

        $warehouse_id = $lims_stock_count_data->warehouse_id;
        $product_id = [];
        $names = [];
        $code = [];
        $qty = [];
        $action = [];

        $file_path = $lims_stock_count_data->final_file
            ? $this->stockCountPath($lims_stock_count_data->final_file)
            : null;
        $file_handle = ($file_path && is_file($file_path)) ? @fopen($file_path, 'r') : false;
        if($file_handle === false){
            return redirect()->back()->with('not_permitted', 'The finalized stock count file is missing. Please upload it again.');
        }

        $i = 0;
        while( !feof($file_handle) ) {
            $current_line = fgetcsv($file_handle);
            if( $current_line && $i > 0 && count($current_line) >= 4 && ($current_line[2] != $current_line[3]) ){
                $product_data = Product::where('code', $current_line[1])->first();
                // Skip rows whose product code no longer resolves rather than
                // blowing up the whole adjustment screen.
                if(! $product_data){
                    $i++;
                    continue;
                }
                $product_id[] = $product_data->id;
                $names[] = $current_line[0];
                $code[] = $current_line[1];

                if($current_line[3])
                    $temp_qty = $current_line[3] - $current_line[2];
                else
                    $temp_qty = $current_line[2] * (-1);

                if($temp_qty < 0){
                    $qty[] = $temp_qty * (-1);
                    $action[] = '-';  
                }
                else{
                    $qty[] = $temp_qty;
                    $action[] = '+';
                }
            }
            $i++;
        }
        fclose($file_handle);

        return view('stock_count.qty_adjustment', compact('lims_warehouse_list', 'warehouse_id', 'id', 'product_id', 'names', 'code', 'qty', 'action'));
    }
    public function destroy($id)
    {
        //
    }
}
