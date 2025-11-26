@extends('layouts.dashboard')

@section('page-title', 'Conversations')

@section('content')
<div class="card">
    @if($chats->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Session</th>
                    <th>Customer</th>
                    <th>Messages</th>
                    <th>Started</th>
                    <th>Last Activity</th>
                    <th>Status</th>
                    <th>Satisfaction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chats as $chat)
                <tr>
                    <td><code style="font-size: 12px;">{{ Str::limit($chat->session_id, 15) }}</code></td>
                    <td>
                        <div>
                            <strong>{{ $chat->customer_name ?? 'Anonymous' }}</strong>
                            @if($chat->customer_email)
                                <br><small style="color: #6b7280;">{{ $chat->customer_email }}</small>
                            @endif
                        </div>
                    </td>
                    <td>{{ $chat->message_count }}</td>
                    <td>{{ $chat->started_at->format('M d, Y H:i') }}</td>
                    <td>{{ $chat->last_message_at ? $chat->last_message_at->diffForHumans() : '-' }}</td>
                    <td>
                        @if($chat->is_resolved)
                            <span class="badge badge-success">Resolved</span>
                        @else
                            <span class="badge badge-warning">Active</span>
                        @endif
                    </td>
                    <td>
                        @if($chat->satisfaction_score)
                            <span style="color: #f59e0b;">
                                {{ str_repeat('⭐', (int)$chat->satisfaction_score) }}
                            </span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('chats.show', $chat) }}" class="btn btn-sm btn-primary">
                            View Chat
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 24px;">
            {{ $chats->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 64px 32px;">
            <div style="font-size: 64px; margin-bottom: 16px;">💬</div>
            <h3 style="color: #1f2937; margin-bottom: 8px;">No conversations yet</h3>
            <p style="color: #6b7280; margin-bottom: 24px;">
                When customers start chatting with your AI bot, their conversations will appear here
            </p>
        </div>
    @endif
</div>
@endsection
