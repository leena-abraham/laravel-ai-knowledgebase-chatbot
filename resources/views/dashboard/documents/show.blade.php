@extends('layouts.dashboard')

@section('page-title', 'Document Details')

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('documents.index') }}" style="color: #6366f1; text-decoration: none;">
        ← Back to Knowledge Base
    </a>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px;">
        <div>
            <h2 style="color: #1f2937; margin-bottom: 8px;">{{ $document->title }}</h2>
            <p style="color: #6b7280;">
                Uploaded by {{ $document->uploader->name }} • {{ $document->created_at->diffForHumans() }}
            </p>
        </div>
        
        <div style="display: flex; gap: 12px;">
            @if($document->status === 'failed')
                <form method="POST" action="{{ route('documents.retry', $document) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Retry Processing</button>
                </form>
            @endif

            <form method="POST" action="{{ route('documents.destroy', $document) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this document?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete Document</button>
            </form>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
        <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
            <div style="color: #6b7280; font-size: 14px;">File Type</div>
            <div style="font-weight: 600; margin-top: 4px;">{{ strtoupper($document->file_type) }}</div>
        </div>
        
        <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
            <div style="color: #6b7280; font-size: 14px;">File Size</div>
            <div style="font-weight: 600; margin-top: 4px;">{{ $document->file_size_human }}</div>
        </div>
        
        <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
            <div style="color: #6b7280; font-size: 14px;">Chunks Created</div>
            <div style="font-weight: 600; margin-top: 4px;">{{ $document->chunk_count }}</div>
        </div>
        
        <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
            <div style="color: #6b7280; font-size: 14px;">Status</div>
            <div style="margin-top: 4px;">
                @if($document->status === 'completed')
                    <span class="badge badge-success">✓ Processed</span>
                @elseif($document->status === 'processing')
                    <span class="badge badge-warning">⏳ Processing</span>
                @elseif($document->status === 'failed')
                    <span class="badge badge-danger">✗ Failed</span>
                @else
                    <span class="badge badge-info">⏸ Pending</span>
                @endif
            </div>
        </div>
    </div>
    
    @if($document->status === 'failed' && $document->error_message)
        <div class="alert alert-error">
            <strong>Processing Error:</strong> {{ $document->error_message }}
        </div>
    @endif
    
    @if($document->content)
        <div>
            <h3 style="margin-bottom: 16px; color: #1f2937;">Extracted Content Preview</h3>
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 14px; line-height: 1.6;">
                {{ Str::limit($document->content, 2000) }}
            </div>
        </div>
    @endif
</div>

@if($document->chunks->count() > 0)
    <div class="card">
        <h3 style="margin-bottom: 16px; color: #1f2937;">Content Chunks ({{ $document->chunks->count() }})</h3>
        <p style="color: #6b7280; margin-bottom: 20px;">
            The document has been split into {{ $document->chunks->count() }} chunks for AI processing.
        </p>
        
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($document->chunks->take(5) as $chunk)
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; border-left: 4px solid #6366f1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <strong>Chunk #{{ $chunk->chunk_index + 1 }}</strong>
                        <span style="color: #6b7280; font-size: 14px;">{{ $chunk->token_count }} tokens</span>
                    </div>
                    <p style="color: #374151; font-size: 14px; line-height: 1.6;">
                        {{ Str::limit($chunk->content, 300) }}
                    </p>
                </div>
            @endforeach
            
            @if($document->chunks->count() > 5)
                <p style="text-align: center; color: #6b7280;">
                    ... and {{ $document->chunks->count() - 5 }} more chunks
                </p>
            @endif
        </div>
    </div>
@endif
@endsection
