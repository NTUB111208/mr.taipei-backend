<?php

namespace App\Http\Controllers;

use App\Models\FavRoute;
use Illuminate\Http\Request;

class FavRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $favRoute = FavRoute::all();
        return $favRoute;
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
        // $body = $request->getBody();
        $body = json_decode($request);
        $user = FavRoute::where('user_id', auth()->user()->user_oauth_id)->where('startPoint', $request->json()->get("startPoint"))->where('destination', $request->json()->get("destination"))->first();
        if ($user) {
            $user->user_id = auth()->user()->user_oauth_id;
            $user->startPoint = $request->json()->get("startPoint");
            $user->destination = $request->json()->get("destination");
            $user->delete();
            return response()->json(['message' => '從收藏路線移除','data'=>$user]);
        } else {
            $favRoute =new FavRoute();
            $favRoute ->user_id =auth()->user()->user_oauth_id;
            // $favRoute ->user_id =$request->json()->auth get("user_id");
            $favRoute ->startPoint	=$request->json()->get("startPoint");
            $favRoute ->destination =$request->json()->get("destination");
            $favRoute ->save();
            return response()->json(['message' => '加入收藏路線','data'=>$favRoute]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FavRoute  $favRoute
     * @return \Illuminate\Http\Response
     */
    public function read(Request $request)
    {
        $select=FavRoute::where('user_id', '=', auth()->user()->user_oauth_id)->get();
        return response()->json(
            ['success' => true, 'FavRoute' => $select]
        );
        // $item = FavRoute::find(auth()->user()->user_oauth_id);
        // if ($item) {
        //     return response()->json(
        //         ['success' => true, 'FavRoute' => $item]
        //     );
        // } else {
        //     return response()->json(
        //         ['success' => false, 'message' => 'Item not found.']
        //     );
        // }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FavRoute  $favRoute
     * @return \Illuminate\Http\Response
     */
    public function edit(FavRoute $favRoute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FavRoute  $favRoute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FavRoute $favRoute)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FavRoute  $favRoute
     * @return \Illuminate\Http\Response
     */
    public function destroy(FavRoute $favRoute)
    {
        //
    }
}
