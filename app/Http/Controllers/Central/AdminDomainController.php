<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminDomainController extends Controller
{
    private const STATUSES = ['Active', 'Inactive'];

    public function index(Request $request): View
    {
        return view('admin.domains.index', ['domains' => $this->paginate($request)]);
    }

    public function pagination(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('admin.domains.partials.pagination', ['domains' => $this->paginate($request)])->render(),
        ]);
    }

    private function paginate(Request $request): mixed
    {
        $perPage = $request->validate(['per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])]])['per_page'] ?? 10;

        return DB::table('domains')
            ->join('tenants', 'tenants.id', '=', 'domains.tenant_id')
            ->select('domains.id', 'domains.domain', 'domains.tenant_id', 'domains.status', 'domains.created_at')
            ->latest('domains.created_at')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1))
            ->withQueryString();
    }

    public function create(): View
    {
        return view('admin.domains.form', ['domain' => null, 'tenants' => $this->tenants(), 'statuses' => self::STATUSES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        DB::table('domains')->insert($validated + ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('admin.domains.index')->with('success', 'Domain created successfully.');
    }

    public function edit(int $domain): View
    {
        $domainRecord = DB::table('domains')->where('id', $domain)->firstOrFail();

        return view('admin.domains.form', ['domain' => $domainRecord, 'tenants' => $this->tenants(), 'statuses' => self::STATUSES]);
    }

    public function update(Request $request, int $domain): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', Rule::unique('domains', 'domain')->ignore($domain)],
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        DB::table('domains')->where('id', $domain)->update($validated + ['updated_at' => now()]);

        return redirect()->route('admin.domains.index')->with('success', 'Domain updated successfully.');
    }

    public function destroy(int $domain): RedirectResponse
    {
        DB::table('domains')->where('id', $domain)->delete();

        return redirect()->route('admin.domains.index')->with('success', 'Domain deleted successfully.');
    }

    private function tenants(): array
    {
        return DB::table('tenants')->select('id')->orderBy('id')->pluck('id', 'id')->all();
    }
}
