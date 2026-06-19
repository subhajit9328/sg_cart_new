@extends('layouts.admin')
@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-2xl font-bold">Categories</h1>
    <a href="{{ route('admin.categories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Category</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<table class="w-full border text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">Parent</th>
            <th class="p-2 text-left">Status</th>
            <th class="p-2 text-left">Order</th>
            <th class="p-2 text-left">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($categories as $cat)
        <tr class="border-t">
            <td class="p-2">
                @if($cat->image)
                    <img src="{{ Storage::url($cat->image) }}" class="w-8 h-8 inline rounded mr-2">
                @endif
                {{ $cat->name }}
            </td>
            <td class="p-2">{{ $cat->parent?->name ?? '—' }}</td>
            <td class="p-2">
                <span class="px-2 py-0.5 rounded text-xs {{ $cat->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td class="p-2">{{ $cat->sort_order }}</td>
            <td class="p-2 flex gap-3">
                <a href="{{ route('admin.categories.edit', $cat) }}" class="text-blue-600">Edit</a>
                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button class="text-red-600">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection