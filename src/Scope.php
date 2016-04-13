<?php namespace MagnesiumOxide\TwitchApi;

abstract class Scope {
    const UserRead = "user_read"; // Read access to non-public user information, such as email address.
    const EditUserBlocks = "user_blocks_edit"; // Ability to ignore or unignore on behalf of a user.
    const ReadUserBlocks = "user_blocks_read"; // Read access to a user's list of ignored users.
    const EditUserFollows = "user_follows_edit"; // Access to manage a user's followed channels.
    const ReadChannel = "channel_read"; // Read access to non-public channel information, including email address and stream key.
    const EditChannel = "channel_editor"; // Write access to channel metadata (game, status, etc).
    const RunCommercial = "channel_commercial"; // Access to trigger commercials on channel.
    const StreamKeyReset = "channel_stream"; // Ability to reset a channel's stream key.
    const ReadSubscribers = "channel_subscriptions"; // Read access to all subscribers to your channel.
    const ReadSubscriptions = "user_subscriptions"; // Read access to subscriptions of a user.
    const CheckSubscription = "channel_check_subscription"; // Read access to check if a user is subscribed to your channel.
    const ChatLogin = "chat_login"; // Ability to log into chat and send messages.
    const ReadFeed = "channel_feed_read"; // Ability to view to a channel feed.
    const EditFeed = "channel_feed_edit"; // Ability to add posts and reactions to a channel feed.

    private function __construct() {}
}