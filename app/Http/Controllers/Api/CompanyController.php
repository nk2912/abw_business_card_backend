<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Resources\CompanyResource;

class CompanyController extends Controller
{
    /**
     * GET ALL companies (public)
     */
    public function index()
    {
        $companies = Company::with('socials')
            ->latest()
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Companies fetched successfully',
            'data'    => CompanyResource::collection($companies),
        ], 200);
    }

    /**
     * GET single company by id (public)
     */
    public function show(Company $company)
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Company fetched successfully',
            'data'    => new CompanyResource($company->load('socials')),
        ], 200);
    }

    /**
     * STORE new company (protected)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string',
            'industry'      => 'required|string',
            'business_type' => 'required|string',
            'description'   => 'nullable|string',
            'address'       => 'nullable|string',
            'website'       => 'nullable|string',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',

            'socials'               => 'array',
            'socials.*.platform'    => 'required|string',
            'socials.*.url'         => 'required|url',
        ]);

        // create company with owner
        $company = Company::create([
            'created_by' => $request->user()->id,
            ...collect($data)->except('socials')->toArray(),
        ]);

        // save socials
        if (!empty($data['socials'])) {
            foreach ($data['socials'] as $social) {
                $company->socials()->create($social);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Company created successfully',
            'data'    => new CompanyResource($company->load('socials')),
        ], 201);
    }

    /**
     * UPDATE company (OWNER ONLY)
     */
    public function update(Request $request, $id)
    {
        $company = Company::withTrashed()->find($id);

        if (!$company) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Company not found',
            ], 404);
        }

        // cannot update deleted company
        if ($company->trashed()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot update a deleted company',
            ], 409);
        }

        // ownership check
        if ($company->created_by !== $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are not allowed to update this company',
            ], 403);
        }

        $data = $request->validate([
            'name'          => 'sometimes|required|string',
            'industry'      => 'sometimes|required|string',
            'business_type' => 'sometimes|required|string',
            'description'   => 'nullable|string',
            'address'       => 'nullable|string',
            'website'       => 'nullable|string',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',

            'socials'               => 'array',
            'socials.*.platform'    => 'required|string',
            'socials.*.url'         => 'required|url',
        ]);

        // update company fields + updated_by
        $company->update([
            'updated_by' => $request->user()->id,
            ...collect($data)->except('socials')->toArray(),
        ]);

        // update socials
        if (array_key_exists('socials', $data)) {
            $company->socials()->delete();
            foreach ($data['socials'] as $social) {
                $company->socials()->create($social);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Company updated successfully',
            'data'    => new CompanyResource($company->load('socials')),
        ], 200);
    }

    /**
     * SOFT DELETE company (OWNER ONLY)
     * Do not allow delete if company has business cards
     */
    public function destroy(Request $request, $id)
    {
        $company = Company::withTrashed()->find($id);

        if (!$company) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Company not found',
            ], 404);
        }

        // already deleted
        if ($company->trashed()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Company already deleted',
            ], 409);
        }

        // ownership check
        if ($company->created_by !== $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are not allowed to delete this company',
            ], 403);
        }

        // business cards exist
        if ($company->businessCards()->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot delete company because it is used by business cards',
            ], 409);
        }

        $company->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Company deleted successfully',
        ], 200);
    }
}
