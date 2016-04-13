<?php

use MagnesiumOxide\TwitchApi\Scope;

return array(
    "ClientId" => "YOUR_CLIENT_ID",
    "ClientSecret" => "YOUR_CLIENT_SECRET",
    "RedirectUri" => "YOUR_REDIRECT_URI",
    "State" => "YOUR_STATE",
    "Scope" => array(
        //Scope::UserRead, /* Read access to non-public user information, such as email address. */
        //Scope::EditUserBlocks, /* Ability to ignore or unignore on behalf of a user. */
        //Scope::ReadUserBlocks, /* Read access to a user's list of ignored users. */
        //Scope::EditUserFollows, /* Access to manage a user's followed channels. */
        //Scope::ReadChannel,/* Read access to non-public channel information, including email address and stream key. */
        //Scope::EditChannel, /* Write access to channel metadata (game, status, etc). */
        //Scope::RunCommercial,  /* Access to trigger commercials on channel. */
        //Scope::StreamKeyReset, /* Ability to reset a channel's stream key. */
        //Scope::ReadSubscribers, /* Read access to all subscribers to your channel. */
        //Scope::ReadSubscriptions, /* Read access to subscriptions of a user. */
        //Scope::CheckSubscription, /* Read access to check if a user is subscribed to your channel. */
        //Scope::ChatLogin, /* Ability to log into chat and send messages. */
        //Scope::ReadFeed, /* Ability to view to a channel feed. */
        //Scope::EditFeed, /* Ability to add posts and reactions to a channel feed. */
    ),
);