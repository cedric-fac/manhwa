<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index()
    {
        return Inertia::render('Clients/Index', [
            'clients' => Client::orderBy('name')->paginate(10)
        ]);
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:15', 'regex:/^\+237\d{9}$/'],
            'address' => ['required', 'string', 'max:255'],
            'tva_rate' => ['required', 'numeric', 'between:0,100']
        ]);

        $client = Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        return Inertia::render('Clients/Show', [
            'client' => $client->load(['readings' => function($query) {
                $query->latest()->take(5);
            }, 'invoices' => function($query) {
                $query->latest()->take(5);
            }])
        ]);
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:15',
                'regex:/^\+237\d{9}$/',
                Rule::unique('clients')->ignore($client->id)
            ],
            'address' => ['required', 'string', 'max:255'],
            'tva_rate' => ['required', 'numeric', 'between:0,100']
        ]);

        $client->update($validated);

        return back()->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
