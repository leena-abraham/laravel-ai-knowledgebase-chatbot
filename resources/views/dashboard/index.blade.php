@extends('layouts.dashboard')

@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card" style="border-left-color: #6366f1;">
        <div class="stat-label">Total Documents</div>
        <div class="stat-value">{{ $stats['total_documents'] }}</div>
        <div class="stat-subtext">{{ $stats['document_limit'] - $stats['total_documents'] }} remaining</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #8b5cf6;">
        <div class="stat-label">Total Conversations</div>
        <div class="stat-value">{{ $stats['total_chats'] }}</div>
        <div class="stat-subtext">All time</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #10b981;">
        <div class="stat-label">Monthly Chats</div>
        <div class="stat-value">{{ $stats['monthly_chats'] }}</div>
        <div class="stat-subtext">{{ $stats['chat_limit'] - $stats['monthly_chats'] }} remaining</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #f59e0b;">
        <div class="stat-label">AI Success Rate</div>
        <div class="stat-value">{{ $successRate }}%</div>
        <div class="stat-subtext">Based on user feedback</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div class="card">
        <div class="card-header">Chat Activity (Last 7 Days)</div>
        <canvas id="chatChart" style="max-height: 300px;"></canvas>
    </div>
    
    <div class="card">
        <div class="card-header">Quick Actions</div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="{{ route('documents.create') }}" class="btn btn-primary" style="text-align: center;">
                📤 Upload Document
            </a>
            <a href="{{ route('documents.index') }}" class="btn" style="background: #f3f4f6; color: #1f2937; text-align: center;">
                📚 View Knowledge Base
            </a>
            <a href="{{ route('chats.index') }}" class="btn" style="background: #f3f4f6; color: #1f2937; text-align: center;">
                💬 View Conversations
            </a>
        </div>
        
        <div style="margin-top: 24px; padding: 16px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
            <strong style="color: #1e40af;">Widget Code</strong>
            <p style="font-size: 14px; color: #1e3a8a; margin-top: 8px;">
                Embed this on your website:
            </p>
            <code style="display: block; background: white; padding: 12px; border-radius: 4px; font-size: 12px; margin-top: 8px; overflow-x: auto;">
                &lt;script src="{{ url('/widget.js') }}" data-company="{{ auth()->user()->company->slug }}"&gt;&lt;/script&gt;
            </code>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">Recent Conversations</div>
    
    @if($recentChats->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Customer</th>
                    <th>Messages</th>
                    <th>Started</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentChats as $chat)
                <tr>
                    <td><code>{{ Str::limit($chat->session_id, 20) }}</code></td>
                    <td>{{ $chat->customer_name ?? $chat->customer_email ?? 'Anonymous' }}</td>
                    <td>{{ $chat->message_count }}</td>
                    <td>{{ $chat->started_at->diffForHumans() }}</td>
                    <td>
                        @if($chat->is_resolved)
                            <span class="badge badge-success">Resolved</span>
                        @else
                            <span class="badge badge-warning">Active</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('chats.show', $chat->id) }}" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #6b7280; padding: 32px;">
            No conversations yet. Share your chat widget to start receiving customer queries!
        </p>
    @endif
</div>

<div class="card">
    <div class="card-header">Most Asked Questions</div>
    
    @if($topQuestions->count() > 0)
        <ul style="list-style: none;">
            @foreach($topQuestions as $question)
            <li style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ Str::limit($question->content, 80) }}</span>
                    <span class="badge badge-info">{{ $question->count }}x</span>
                </div>
            </li>
            @endforeach
        </ul>
    @else
        <p style="text-align: center; color: #6b7280; padding: 32px;">
            No questions asked yet.
        </p>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chatChart');
    const chartData = @json($chatsByDay);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Chats',
                data: chartData.map(d => d.count),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
