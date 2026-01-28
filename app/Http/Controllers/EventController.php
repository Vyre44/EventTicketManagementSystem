<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class EventController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'organizer') {
            $events = Event::where('organizer_id', auth()->id())->get();
        } else {
            $events = Event::all();
        }
        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        return view('events.show', compact('event'));
    }

    public function create()
    {
        $this->authorize('create', Event::class);
        return view('events.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);
        // Store işlemleri burada olacak
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        // Update işlemleri burada olacak
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();
        return redirect()->route('events.index');
    }
}
