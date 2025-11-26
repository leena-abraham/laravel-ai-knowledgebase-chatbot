@extends('layouts.dashboard')

@section('page-title', 'Search Knowledge Base')

@section('content')
<div class="card">
    <div class="card-header">Search Knowledge Base</div>
    
    <form method="GET" action="{{ route('documents.search') }}" style="margin-bottom: 24px;">
        <div style="display: flex; gap: 12px;">
            <input 
                type="text" 
                name="query" 
                value="{{ $query ?? '' }}" 
                placeholder="Search your knowledge base..." 
                style="flex: 1; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;"
                autofocus
            >
            <button type="submit" class="btn btn-primary">
                🔍 Search
            </button>
        </div>
    </form>
    
    @if(isset($query) && $query)
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 18px; color: #374151; margin-bottom: 16px;">
                Search Results for: <strong>"{{ $query }}"</strong>
            </h3>
            
            @if(count($results) > 0)
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Found {{ count($results) }} relevant chunks
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($results as $result)
                        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; border-left: 4px solid #6366f1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                <div>
                                    <h4 style="font-size: 16px; font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                                        📄 {{ $result['document']->title }}
                                    </h4>
                                    <p style="font-size: 14px; color: #6b7280;">
                                        Chunk #{{ $result['chunk']->chunk_number }}
                                    </p>
                                </div>
                                <div>
                                    <span class="badge badge-info">
                                        {{ round($result['similarity'] * 100, 1) }}% match
                                    </span>
                                </div>
                            </div>
                            
                            <div style="background: white; padding: 16px; border-radius: 6px; margin-top: 12px;">
                                <p style="color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $result['chunk']->content }}</p>
                            </div>
                            
                            <div style="margin-top: 12px;">
                                <a href="{{ route('documents.show', $result['document']->id) }}" class="btn btn-sm btn-primary">
                                    View Full Document
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 48px; background: #f9fafb; border-radius: 8px;">
                    <p style="font-size: 18px; color: #6b7280; margin-bottom: 8px;">
                        😕 No results found
                    </p>
                    <p style="color: #9ca3af;">
                        Try different keywords or upload more documents to your knowledge base.
                    </p>
                </div>
            @endif
        </div>
    @else
        <div style="text-align: center; padding: 48px; background: #f9fafb; border-radius: 8px;">
            <p style="font-size: 18px; color: #6b7280; margin-bottom: 8px;">
                🔍 Search your knowledge base
            </p>
            <p style="color: #9ca3af;">
                Enter a query above to find relevant information from your documents.
            </p>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header">Tips for Better Search Results</div>
    <ul style="list-style: none; padding: 0;">
        <li style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
            <strong>✓ Use specific keywords:</strong> Instead of "product", try "product pricing" or "product features"
        </li>
        <li style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
            <strong>✓ Ask questions:</strong> "How do I reset my password?" works better than just "password"
        </li>
        <li style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
            <strong>✓ Use natural language:</strong> The AI understands context and semantics
        </li>
        <li style="padding: 12px;">
            <strong>✓ Keep it concise:</strong> Short, focused queries often yield better results
        </li>
    </ul>
</div>
@endsection
