<?php

namespace local_evokegame;

abstract class badgecriteria {
    protected $userid;
    protected $badgecriteria;

    public function __construct(int $userid, \stdClass $badgecriteria) {
        $this->userid = $userid;
        $this->badgecriteria = $badgecriteria;
    }

    public abstract function user_achieved_criteria(): bool;
    public abstract function get_user_criteria_progress(): int;
}
