@forelse ($dataPolicies as $dataPolicy)
    <tr>
        <td>{{ $dataPolicy->name }}</td>
        <td>{{ $dataPolicy->code }}</td>
        <td>
            @if ($dataPolicy->self_only)
                <span class="badge badge-soft-info">Self only</span>
            @else
                <span class="badge badge-soft-primary">Restricted</span>
            @endif
        </td>
        <td>
            <span class="badge {{ $dataPolicy->status ? 'badge-soft-success' : 'badge-soft-danger' }}">
                {{ $dataPolicy->status ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-end">
            <div class="d-flex justify-content-end align-items-center">
                <a href="{{ url('data-policy/'.$dataPolicy->id.'/edit') }}"
                    class="btn btn-sm btn-light me-2" title="Edit">
                    <i class="ti ti-edit"></i>
                </a>
                <form action="{{ url('data-policy/'.$dataPolicy->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this data policy?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                        <i class="ti ti-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-4 text-muted">No data policies found.</td>
    </tr>
@endforelse
