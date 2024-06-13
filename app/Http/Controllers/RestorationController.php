<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Restoration;
use App\Models\StuffStock;
use App\Models\Lending;
use Illuminate\Http\Request;

class RestorationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                'user_id' => 'required',
                'lending_id' => 'required',
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
            ]);
        
        $getLending = Lending::where('id', $request->lending_id)->first();
        $totalStuff = $request->total_good_stuff + $request->total_defec_stuff;
        if ($getLending['total_stuff'] != $totalStuff) {
            return ApiFormatter::sendResponse(400, 'The amount of items returned does not match the amount borrowed');
        } else {
            $getStuffStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first();

            $createRestoration = Restoration::create([
                'user_id' => $request->user_id,
                'lending_id' => $request->lending_id,
                'date_time' => $request->date_time,
                'total_good_stuff' => $request->total_good_stuff,
                'total_defec_stuff' => $request->total_defec_stuff, 
            ]);

            $updateStock = $getStuffStock->update([
                'total_available' => $getStuffStock['total_available'] + 
                $request->total_good_stuff,
                'total_defec' => $getStuffStock['total_defec'] +
                $request->total_defec_stuff,
            ]);

            if ($createRestoration && $updateStock) {
                return ApiFormatter::sendResponse(200, 'Succesfully Create A Restoration Data', $createRestoration);
            }
        } 
        } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, $err->getMessage());
}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function show(Restoration $restoration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function edit(Restoration $restoration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Restoration $restoration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function destroy(Restoration $restoration)
    {
        
    }
}
