<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrganizationMemberResource;
use Illuminate\Http\Request;

class OrganizationMemberController extends Controller
{
    public function index(Request $request)
    {
        $organization = $request->user()->currentOrganization;

        $members = $organization->users()
            ->orderBy('name')
            ->get();

        return OrganizationMemberResource::collection($members);
    }
}
