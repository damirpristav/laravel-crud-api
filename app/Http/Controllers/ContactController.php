<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Contact::query();
        $request_query = $request->query();

        $perPage = !empty($request_query['per_page']) ? $request_query['per_page'] : 15;

        if (!empty($request_query['search'])) {
            $search = $request_query['search'];
            $data->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if (!empty($request_query['birth_date'])) {
            $date = $request_query['birth_date'];
            $data->where('birth_date', '>=', $date);
        }

        if (!empty($request_query['birth_date_before'])) {
            $date = $request_query['birth_date_before'];
            $data->where('birth_date', '<=', $date);
        }

        if (!empty($request_query['sort_by'])) {
            $orderBy = $request_query['sort_by'];
            $orderDirection = 'asc';
            if (!empty($request_query['sort_direction'])) {
                $orderDirection = $request_query['sort_direction'];
            }
            $data->orderBy($orderBy, $orderDirection);
        }

        return response()->json($data->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'string|nullable',
            'address' => 'string|nullable',
            'birth_date' => 'date|nullable',
        ]);

        $contactExists = Contact::where('email', $fields['email'])->first();
        if ($contactExists) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Contact with this email address already exists.',
            ], 400);
        }

        $contact = Contact::create($fields);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'data' => null,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Contact not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Contact not found',
            ], 404);
        }

        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'string|nullable',
            'address' => 'string|nullable',
            'birth_date' => 'date|nullable',
        ]);

        if ($fields['email'] !== $contact->email) {
            $contactExists = Contact::where('email', $fields['email'])->first();
            if ($contactExists) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Contact with this email address already exists.',
                ], 400);
            }
        }

        $contact->update($fields);

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Contact not found',
            ], 404);
        }

        $contact->delete();

        return response(status: 204);
    }
}
