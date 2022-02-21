<?php

namespace Core;

class Errors{
    const CODE_1 = "The server tried putting you in an invalid game-mode.";
    const CODE_2 = "The server tried putting you in an invalid mini-game.";
    const CODE_3 = "The server tried initializing you into a game with an invalid game-mode or mini-game.";
    const CODE_4 = "The server tried triggering the respawn system while you were in the lobby.";
    const CODE_5 = "The server detected that you have no KB region selected.";
    const CODE_6 = "The server tried using a wrong object while setting up who tagged you.";
    const CODE_7 = "The server tried using something other than an object or string while setting up who tagged you.";
    const CODE_8 = "The server tried using an invalid string/player name while setting up who tagged you.";
    const CODE_9 = "The server tried using an invalid string/player name while setting up who the server should hide.";
    const CODE_10 = "The server tried deleting your game session while it was null while initializing your lobby. ";
}