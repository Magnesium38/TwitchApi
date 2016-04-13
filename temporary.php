<?php

class Status {
    /**
     * Methods to implement for API Docs
     */
    /**
     * Blocks           Done
     *      GET /users/:user/blocks                             Done
     *      PUT /users/:user/blocks/:target                     Done
     *      DELETE /users/:user/blocks/:target                  Done
     */
    /**
     * Channels         Done
     *      GET /channels/:channel                              Done
     *      GET /channel                                        Done
     *      GET /channels/:channel/videos                       Done
     *      GET /channels/:channel/follows                      Done
     *      GET /channels/:channel/editors                      Done
     *      PUT /channels/:channel                              Done
     *      DELETE /channels/:channel                           Done
     *      POST /channels/:channel/commercial                  Done
     *      GET /channels/:channel/teams                        Done
     */
    /**
     * Channel Feed     Done
     *      GET /feed/:channel/posts                            Done
     *      POST /feed/:channel/posts                           Done
     *      GET /feed/:channel/posts/:id                        Done
     *      DELETE /feed/:channel/posts/:id                     Done
     *      POST /feed/:channel/posts/:id/reactions             Done
     *      DELETE /feed/:channel/posts/:id/reactions           Done
     *
     * All of these need to check for authentication and other channels.
     * Tough though, because this is a beta feature.
     */
    /**
     * Chat             Done
     *      GET /chat/:channel                                  Done
     *      GET /chat/:channel/badges                           Done
     *      GET /chat/emoticons                                 Done
     *      GET /chat/emoticon_images                           Done
     */
    /**
     * Follows          Done
     *      GET /channels/:channel/follows                      Done
     *      GET /users/:user/follows/channels                   Done
     *      GET /users/:user/follows/channels/:target           Done
     *      PUT /users/:user/follows/channels/:target           Done
     *      DELETE /users/:user/follows/channels/:target        Done
     *      GET /streams/followed                               Done
     */
    /**
     * Games            Done
     *      GET /games/top                                      Done
     */
    /**
     * Ingests          Done
     *      GET /ingests                                        Done
     */
    /**
     * Root             Done
     *      GET /                                               Done
     */
    /**
     * Search           Done
     *      GET /search/channels                                Done
     *      GET /search/streams                                 Done
     *      GET /search/games                                   Done
     */
    /**
     * Streams          Done
     *      GET /streams/:channel                               Done
     *      GET /streams                                        Done
     *      GET /streams/featured                               Done
     *      GET /streams/summary                                Done
     *      GET /streams/followed                               Done
     */
    /**
     * Subscriptions    Done
     *      GET /channels/:channel/subscriptions                Done
     *      GET /channels/:channel/subscriptions/:user          Done
     *      GET /users/:user/subscriptions/:channel             Done
     */
    /**
     * Teams            Done
     *      GET /teams                                          Done
     *      GET /teams/:team                                    Done
     */
    /**
     * Users            Done
     *      GET /users/:user                                    Done
     *      GET /user                                           Done
     *      GET /streams/followed                               Done
     *      GET /videos/followed                                Done
     */
    /**
     * Videos           Done
     *      GET /videos/:id                                     Done
     *      GET /videos/top                                     Done
     *      GET /channels/:channel/videos                       Done
     *      GET /videos/followed                                Done
     */
}