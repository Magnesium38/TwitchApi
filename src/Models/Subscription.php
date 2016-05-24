<?php namespace MagnesiumOxide\TwitchApi\Model;

class Subscription extends CreatableModel {
    /**
     * Returns the internal id.
     *
     * @return string|null
     */
    public function getId() {
        return $this->getHelper("_id");
    }

    /**
     * Returns the user that the subscription object is for.
     *
     * @return null|User
     */
    public function getUser() {
        return User::create($this->getHelper("user"));
    }

    /**
     * Returns when the subscription was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        $datetime = $this->getHelper("created_at");

        if (is_null($datetime)) {
            return null;
        }

        return new \DateTime($datetime);
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
