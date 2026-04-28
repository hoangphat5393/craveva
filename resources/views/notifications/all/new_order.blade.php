@php
    use App\Models\Order;

    $order = Order::find($notification->data['id']);
    $user = $order?->client;
    $subject = !in_array('client', user_roles()) ? __('email.orders.subject') : __('email.order.subject');
@endphp

<x-cards.notification :notification="$notification" :link="route('orders.show', $notification->data['id'])" :image="$user?->image_url ?: company()->logo_url" :title="$subject" :text="$notification->data['order_number']" :time="$notification->created_at" />
