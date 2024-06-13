<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use App\Models\InboundStuff;
use App\Models\Stuffstock;                                                      
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InboundStuffController extends Controller
{
    
    public function index(request $request)
    {
        try{
            if($request->filter_id){
               $data = InboundStuff::where('stuff_id', $request->filter_id)->with('stuff','stuff.stuffStock')->get();
            }else{
                $data = InboundStuff::all();
            }
            return ApiFormatter::sendResponse(200, 'succes', $data);
           }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request',$err->getMessage());
           }
    }
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proff_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            if($request->hasFile('proff_file')) {
                $proof = $request->file('proff_file'); 
                $destinationPath = 'proof/'; // destionationPath = untuk memasukan file ke folder tujuan 
                $proofName = date('YmdHis') . "." . $proof->getClientOriginalExtension();
                $proof->move($destinationPath, $proofName); 
            }
            $createStock = InboundStuff::create([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                'proff_file' => $proofName,
            ]);

            if ($createStock){
                $getStuff = Stuff::where('id', $request->stuff_id)->first();
                $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();

                if (!$getStuffStock){
                    $updateStock = StuffStock::create([
                        'stuff_id' => $request->stuff_id,
                        'total_available' => $request->total,
                        'total_defec' => 0,
                    ]);
                } else {
                    $updateStock = $getStuffStock->update([
                        'stuff_id' => $request->stuff_id,
                        'total_available' =>$getStuffStock['total_available'] + $request->total,
                        'total_defec' => $getStuffStock['total_defec'],
                    ]);
                }

                if ($updateStock) {
                    $getStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
                    $stuff = [
                        'stuff' => $getStuff,
                        'InboundStuff' => $createStock,
                        'stuffStock' => $getStock
                    ];

                    return ApiFormatter::sendResponse(200, 'Successfully Create A Inbound Stuff Data', $stuff);
                } else {
                    return ApiFormatter::sendResponse(400, false, 'Failed To Update A Stuff Stock Data');
                }
            } else {
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    public function show($id)
    {
    
        try {
            $getInboundStuff = InboundStuff::with('stuff', 'suff.stuffStock')->find($id);
            
            if (!$getInboundStuff) {
                return ResponseFormatter::sendResponse(404, 'Data Inbound Stuff Not Found');
            } else {
                return ResponseFormatter::sendResponse(200, 'Successfully Get A Inbound Stuff Data', $getInboundStuff);
            }
        } catch (\Exception $e) {
            return ResponseFormatter::sendResponse(400, $e->getMessage());
        }

    }

    public function update(Request $request, $id)
    {
        try {
            //get daya inbound yang mau diupdate
            $getInboundStuff = InboundStuff::find($id);//find => mencari sesuai pk

            if (!$getInboundStuff) { //kalau inbound gaada
                return ApiFormatter::sendResponse(404, "Data Inbound Stuff Not Found");
            } else { //data inbound ada
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'total' => 'required',
                    'date' => 'required',
                ]);
            }

                if ($request->hasFile('proof_file')) {//ini jika ada request proof file
                    $proof = $request->file('proof_file');
                    $destinationPath = 'proof/';
                    $proofName = date('YmdHis') . "." .
                    $proof->getClientOriginalExtension();
                    $proof->move($destinationPath, $proofName);

                    // unlink(base_path('public/proof/' . $getInboundStuff
                    // ['proof_file']));
                } else {//kalau gaada pake data dari get inbound di awal
                    $proofName = $getInboundStuff['proof_file'];
                }

                $getStuff = Stuff::where('id', $getInboundStuff['stuff_id'])->first();
                //get data stuff stock berdasarkan stuff id di variable awal
                $getStuffStock = StuffStock::where('stuff_id', $getInboundStuff['stuff_if'])->first();//stuff_id request tidak merubah

                $getCurrentStock = StuffStock::where('stuff_id', $request['stuff_id'])->first(); //tuff_id request berubah

                if ($getStuffStock['stuff_id'] == $request ['stuff_id']) {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] - 
                        $getInboundStuff['total'] + $request->total,
                    ]);//update data yang stuff_id tidak berubah dengan merubah total available dikurangi total data lama di tambah total baru
                } else {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] -
                        $getInboundStuff['total'],
                    ]); //update data yang stuff_id tidak berubah dengan mengurangi total available dengan data yang lama

                    $updateStock = $getCurrentStock->update([
                        'total_avaliable' => $getStuffStock['total_available'] - 
                        $getInboundStuff['total'],
                    ]);//update data stuff id yang berubah dengan menjumlahkan total available dengan total yang baru
                }

                $updateInbound = $getInboundStuff->update([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff-file'=>$request->proofName,
                ]);

                $getStock = StuffStock::where('stuff_id', $request['stuff_id'])->first();
                $getInbound = InboundStuff::find($id)->with('stuff', 'stuffstock');
                $getCurrentStuff = Stuff::where('id', $request['stuff_id'])->first();

                $stuff = [
                    'stuff' => $getCurrentStuff,
                    'inboundStuff' => $getInbound,
                    'stuffStock' => $getStock,
                ];

                return ApiFormatter::SendResponse(200, "Succesfully Update A Inbound Stuff Data", $stuff);
        } catch (\Throwable $err){
            return ApiFormatter::SendResponse(400, $err->getMessage()); 
        {

        }
    }
    }
       



    public function trash()
    {
        try{
            $data= InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    
    public function restore(InboundStuff $inboundStuff, $id)
    {
        try {
            // Memulihkan data dari tabel 'inbound_stuffs'
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($checkProses) {
                // Mendapatkan data yang dipulihkan
                $restoredData = InboundStuff::find($id);
    
                // Mengambil total dari data yang dipulihkan
                $totalRestored = $restoredData->total;
    
                // Mendapatkan stuff_id dari data yang dipulihkan
                $stuffId = $restoredData->stuff_id;
    
                // Memperbarui total_available di tabel 'stuff_stocks'
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                
                if ($stuffStock) {
                    // Menambahkan total yang dipulihkan ke total_available
                    $stuffStock->total_available += $totalRestored;
    
                    // Menyimpan perubahan pada stuff_stocks
                    $stuffStock->save();
                }
    
                return ApiFormatter::sendResponse(200, 'success', $restoredData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent(InboundStuff $inboundStuff, Request $request, $id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proof/'.$getInbound->proof_file));
            // Menghapus data dari database
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
    
            // Memberikan respons sukses
            return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            // Memberikan respons error jika terjadi kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }   
    
    private function deleteAssociatedFile(InboundStuff $inboundStuff)
    {
        // Mendapatkan jalur lengkap ke direktori public
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proof';

    
        // Menggabungkan jalur file dengan jalur direktori public
         $filePath = public_path('proof/'.$inboundStuff->proof_file);
    
        // Periksa apakah file ada
        if (file_exists($filePath)) {
            // Hapus file jika ada
            unlink(base_path($filePath));
        }
    }
    
}