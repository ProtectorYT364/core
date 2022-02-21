<?php

namespace Party;

use pocketmine\Player;

class PartyHandler {

    /**
     * @var Party|array
     */
    public Party|array $parties = [];

    /**
     * @param Player $owner
     * Creates a party.
     */
    public function createParty(Player $owner)
    {

        $party = new Party($owner);
        $this->parties[] = $party;

    }

    /**
     * @param Party $party
     * Unsets a party. Used by Party::delete()
     */
    public function deleteParty(Party $party)
    {
        unset($party);
    }

    /**
     * @return Party|array
     * Returns all the current parties.
     */
    public function getParties(): Party|array
    {
        return $this->parties;
    }

}