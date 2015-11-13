<?php

namespace Vinelab\NeoEloquent\Exceptions;

// tomb - I changed this from extends NeoEloquentException to Exception because I think the name
// of the base class was refactored, however there is no test that excercises this Exception
// so my change is untested.  At a glance it looks like such a test would be hard to write
// because I think this exception can only be thrown if there are mistakes in the NeoEloquent code
class InvalidCypherGrammarComponentException extends Exception
{
}
