<?php

it('has console/commands/pestinstallcommand page', function () {
    $response = $this->get('/console/commands/pestinstallcommand');

    $response->assertStatus(200);
});
