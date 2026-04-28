@php
    use App\Models\Project;
    $notificationUser = Project::find($notification->data['id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('projects.show', $notification->data['id'])" :image="optional($notificationUser->client)->image_url ?: company()->logo_url" :title="__('email.rating.subject')" :text="$notification->data['project_name']" :time="$notification->created_at" />
@endif
