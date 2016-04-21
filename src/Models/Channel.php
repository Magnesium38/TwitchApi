<?php namespace MagnesiumOxide\TwitchApi\Model;

class Channel extends BaseModel {
    public function getMature() {
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

    public function getLanguage() {
        return $this->getHelper("language");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getUsername() {
        return $this->getHelper("name");
    }

    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getUpdatedAt() {
        return $this->getHelper("updated_at");
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

    public function getPartnerStatus() {
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

    public function getLinks() {
        return $this->getHelper("_links");
    }
}
