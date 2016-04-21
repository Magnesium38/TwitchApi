<?php namespace MagnesiumOxide\TwitchApi\Model;

class Channel extends BaseModel {
    public function isMature() {
        return $this->getHelper("mature");
    }

    public function getStatus() {
        return $this->getHelper("status");
    }

    public function getBroadcasterLanguage() {
        return $this->getHelper("broadcaster_language");
    }

    public function getDisplayName() {
        return $this->getHelper("display_name");
    }

    public function getGame() {
        return $this->getHelper("game");
    }

    public function getDelay() {
        return $this->getHelper("delay");
    }

    /**
     * I think this is the same as broadcaster_language. So I'm hiding this one since it seems like a duplicate.
     *
     * @return mixed
     */
    protected function getLanguage() {
        return $this->getHelper("language");
    }

    /**
     * Returns the internal id of the object.
     * I don't see anywhere in the API where this value could be used.
     *
     * @return mixed
     */
    public function getId() {
        return $this->getHelper("_id");
    }

    public function getUsername() {
        return $this->getHelper("name");
    }

    public function getCreatedAt() {
        return new \DateTime($this->getHelper("created_at"));
    }

    public function lastUpdatedAt() {
        return new \DateTime($this->getHelper("updated_at"));
    }

    public function getLogo() {
        return $this->getHelper("logo");
    }

    public function getBanner() {
        return $this->getHelper("banner");
    }

    public function getVideoBanner() {
        return $this->getHelper("video_banner");
    }

    public function getBackground() {
        return $this->getHelper("background");
    }

    public function getProfileBanner() {
        return $this->getHelper("profile_banner");
    }

    public function getProfileBannerBackgroundColor() {
        return $this->getHelper("profile_banner_background_color");
    }

    public function isPartner() {
        return $this->getHelper("partner");
    }

    public function getUrl() {
        return $this->getHelper("url");
    }

    public function getNumViews() {
        return $this->getHelper("views");
    }

    public function getNumFollowers() {
        return $this->getHelper("followers");
    }

    /**
     * Returns the self-referencing links provided by the API.
     * I don't see a reason to expose this method. Leaving protected in case there's a niche use case.
     *
     * @return array|null
     */
    protected function getLinks() {
        return $this->getHelper("_links");
    }
}
