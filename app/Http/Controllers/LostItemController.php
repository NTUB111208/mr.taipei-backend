<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LostItemController extends Controller
{
    static $RULES = [
        'lost_item_name' => 'required|string|max:255',
        'lost_item_image' => 'required|string|max:255',
        'lost_item_description' => 'nullable|string|max:255',
        'lost_item_location' => 'required|string|max:255',
        'lost_item_color' => 'required|string|max:255',
        'lost_item_status' => 'nullable|string|max:255',
    ];

    public function list()
    {
        return response()->json(
            ['items' => LostItem::all()]
        );
    }

    public function create(Request $request)
    {
        $data = $request->json();
        Validator::make($data->all(), self::$RULES)->validate();

        $item = LostItem::create([
            'lost_item_name' => $request->input('lost_item_name'),
            'lost_item_image' => $request->input('lost_item_image'),
            'lost_item_description' => $request->input('lost_item_description'),
            'lost_item_location' => $request->input('lost_item_location'),
            'lost_item_color' => $request->input('lost_item_color'),
            'created_by' => auth()->user()->user_id,
            'updated_by' => auth()->user()->user_id,

        ]);
        return response()->json(
            ['success' => true, 'item' => $item]
        );
    }

    public function read(Request $request, $lostItemID)
    {
        $item = LostItem::find($lostItemID);
        if ($item) {
            return response()->json(
                ['success' => true, 'item' => $item]
            );
        } else {
            return response()->json(
                ['success' => false, 'message' => 'Item not found.']
            );
        }
    }

    public function update(Request $request, $lostItemID)
    {
        $data = $request->json();
        Validator::make($data->all(), self::$RULES)->validate();

        $item = LostItem::find($lostItemID);
        if (!$item) {
            return response()->json(
                ['success' => false, 'message' => '找不到物品']
            );
        }

        if (Auth::user()->user_is_admin) {
            $item->update([
                'lost_item_name' => $request->input('lost_item_name'),
                'lost_item_image' => $request->input('lost_item_image'),
                'lost_item_description' => $request->input('lost_item_description'),
                'lost_item_location' => $request->input('lost_item_location'),
                'lost_item_color' => $request->input('lost_item_color'),
                'lost_item_status' => $request->input('lost_item_status'),
                'updated_by' => auth()->user()->user_id,
            ]);
        } else {
            if ($item->created_by == Auth::user()->user_id) {
                $item->update([
                    'lost_item_name' => $request->input('lost_item_name'),
                    'lost_item_image' => $request->input('lost_item_image'),
                    'lost_item_description' => $request->input('lost_item_description'),
                    'lost_item_location' => $request->input('lost_item_location'),
                    'lost_item_color' => $request->input('lost_item_color'),
                    'updated_by' => auth()->user()->user_id,
                ]);
            } else {
                return response()->json(
                    ['success' => false, 'message' => '你沒有權限更新此物品']
                );
            }
        }
        return response()->json(
            ['success' => true, 'item' => $item]
        );
    }

    public function delete($lostItemID)
    {
        $lostItem = LostItem::find($lostItemID);
        if (!$lostItem) {
            return response()->json([
                'status' => false,
                'message' => '找不到物品'
            ], 404);
        }

        $user = Auth::user();
        if ($user->user_is_admin) {
            $lostItem->delete();
        } else {
            if ($lostItem->created_by == $user->user_id) {
                $lostItem->delete();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => '你沒有權限刪除此物品'
                ], 403);
            }
        }

        return response()->json([
            'status' => true,
            'message' => '物品已刪除'
        ], 200);
    }
}
