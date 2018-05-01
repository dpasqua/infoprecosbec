<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->get('/');

        $contents = $this->response->getContent();
        $this->assertEquals(
            //$this->app->version(), $this->response->getContent()
            "<!DOCTYPE html>\n<html lan", substr($contents, 0, 25)
        );
    }
}
