<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SimulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Simulations/Index', [
            'simulations' => Auth::user()->simulations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Simulations/Edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Gate::allows('create-simulation');

        $validated = $request->validate([
            'name' => 'string|nullable',
            'general' => 'nullable',
            'rent' => 'nullable',
            'buy' => 'nullable',
        ]);

        $simulation = $request->user()->simulations()->create($validated);

        return redirect()->route('simulations.edit', $simulation);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Simulation  $simulation
     * @return \Illuminate\Http\Response
     */
    public function show(Simulation $simulation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Simulation  $simulation
     * @return \Illuminate\Http\Response
     */
    public function edit(Simulation $simulation)
    {
        return Inertia::render('Simulations/Edit', [
            'simulation' => $simulation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Simulation  $simulation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Simulation $simulation)
    {
        $validated = $request->validate([
            'name' => 'string|nullable',
            'general' => 'nullable',
            'rent' => 'nullable',
            'buy' => 'nullable',
        ]);

        $simulation = $simulation->update($validated);

        return back()->with('sucess', 'Sauvegardé');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Simulation  $simulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Simulation $simulation)
    {
        $simulation->delete();

        return redirect()->route('simulations.create')
            ->with('sucess', 'Simulation supprimée');
    }
}
