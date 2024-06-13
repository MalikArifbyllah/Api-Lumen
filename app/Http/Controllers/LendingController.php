<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\Stuffstock;

class LendingController extends Controller
{
   
    public function index()
    {
        try {
            $getLending = Lending::with('stuff','user')->get();

            return ApiFormatter::sendResponse(200, 'Succesfully Get All Lending Data' , $getLending);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
            ]);

            

            $createLending = Lending::create([
                'stuff_id' => $request->stuff_id,
                'date_time' => $request->date_time,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'notes' => $request->notes,
                'total_stuff' => $request->total_stuff,
            ]);

            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
            $updateStock  = $getStuffStock->update([
                'total_available' => $getStuffStock['total_available'] -
                $request->total_stuff,
            ]);

            return ApiFormatter::sendResponse(200,   'Successfully Create A Lending Data', $createLending);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $getLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, 'Data Lending Not Found');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get A Lending Data', $getLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function edit(Lending $lending)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            
            $getLending = Lending::find($id);

            if ($getLending) {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'date_time' => 'required',
                    'name' => 'required',
                    'user_id' => 'required',
                    'notes' => 'required',
                    'total_stuff' => 'required',
                ]);

            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();// get stock berdasarkan request stuff id 
            $getCurrentStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first(); // stuff_id request tidak berubah

           if ($request->stuff_id == $getCurrentStock['stuff_id']) {
            $updateStock =  $getCurrentStock->update([
                'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'] - $request->total_stuff
            ]); // total available lama yang akan dijumlahkan dengan total peminjaman barang lama lalu dikurangai dengan total peminjaman yang baru
           
        } else {
        $updateStock = $getCurrentStock->update([
         'total_available' => $getCurrentStock['total_available'] + $request['total_stuff'],
        ]); // total available lama dijumllahkan dengan total peminjaman barang yang lama 

        $updateStock = $getStuffStock->update([
         'total_available' => $getStuffStock['total_available'] - $request['total_stuff'],
        ]); // total available baru dikurangi dengan total peminjaman yang baru 
        }

        $updateLending = $getLending->update([
            'stuff_id' => $request->stuff_id,
            'date_time' => $request->date_time,
            'name' => $request->name,
            'user_id' => $request->user_id,
            'notes' => $request->notes,
            'total_stuff' => $request->total_stuff,
        ]);

        $getUpdateLending = lending::where('id',$id)->with('stuff', 'user', 'restoration')->first();

        return ApiFormatter::sendResponse(200, 'Successfully Update A Lending Data', $getUpdateLending);
    }

        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lending  $lending
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $getLending = Stuff::where('id' ,$id)->delete();
            
            return ApiFormatter::sendResponse(200, 'success', 'Data stuff berhasil di hapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request' , $err->getMessage());
    
    }
}
}