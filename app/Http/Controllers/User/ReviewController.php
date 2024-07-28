<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Review;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function StoreReview(Request $request)
    {





        $product = $request->product_id;
        $vendor = $request->hvendor_id;

        $user = Auth::id();

        // Check if the user has purchased the product
        $hasPurchased = OrderItem::where('product_id', $product)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->exists();

        if (!$hasPurchased) {

            $notification = array(
                'message' => 'You can only review products you have purchased.',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);




            // return response()->json(['message' => ], 403);
        }


        $request->validate([
            'comment' => 'required',
        ]);

        Review::insert([

            'product_id' => $product,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'rating' => $request->quality,
            'vendor_id' => $vendor,
            'created_at' => Carbon::now(),

        ]);

        $notification = array(
            'message' => 'Review Will Approve By Admin',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } // End Method 



    public function PendingReview()
    {

        $review = Review::where('status', 0)->orderBy('id', 'DESC')->get();
        return view('backend.review.pending_review', compact('review'));
    } // End Method 


    public function ReviewApprove($id)
    {

        Review::where('id', $id)->update(['status' => 1]);

        $notification = array(
            'message' => 'Review Approved Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } // End Method 

    public function PublishReview()
    {

        $review = Review::where('status', 1)->orderBy('id', 'DESC')->get();
        return view('backend.review.publish_review', compact('review'));
    } // End Method 


    public function ReviewDelete($id)
    {

        Review::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Review Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } // End Method 


    public function VendorAllReview()
    {

        $id = Auth::user()->id;

        $review = Review::where('vendor_id', $id)->where('status', 1)->orderBy('id', 'DESC')->get();
        return view('vendor.backend.review.approve_review', compact('review'));
    } // End Method 


}
