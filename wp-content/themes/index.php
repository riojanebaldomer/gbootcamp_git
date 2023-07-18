<?php
class c
{
   public function __clone()
   {
       clone $this;
   }
}
clone new c();
// Silence is golden.
