<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlQuery;

class SodaDatasetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SodaClient
     */
    private $client;

    private $id;
    private $domain;
    private $token;

    public static function invalidResourceIDs ()
    {
        return array(
            array("pkfj5jsd"),
            array("pk#j-5j!d"),
            array("1234-werwe"),
            array("123--4545")
        );
    }

    public function setUp ()
    {
        $this->id     = "pkfj-5jsd";
        $this->domain = "opendata.socrata.com";
        $this->token  = "khpKCi1wMz2bwXyMIHfb6ux73";

        $this->client = new SodaClient($this->domain, $this->token);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidClient ()
    {
        new SodaDataset(NULL, "qwer-1234");
    }

    /**
     * @dataProvider invalidResourceIDs
     * @expectedException allejo\Socrata\Exceptions\InvalidResourceException
     *
     * @param $resourceID string The resource ID to be testing
     */
    public function testInvalidResourceIDs ($resourceID)
    {
        new SodaDataset($this->client, $resourceID);
    }

    /**
     * @expectedException allejo\Socrata\Exceptions\HttpException
     * @expectedExceptionCode 403
     */
    public function testGetDatasetWithInvalidCredentials ()
    {
        $sc = new SodaClient($this->domain, $this->token, "email@example.org", "foobar");
        $ds = new SodaDataset($sc, "pkfj-5jsd");

        $ds->getDataset();
    }

    public function testGetMetadata ()
    {
        $ds = new SodaDataset($this->client, $this->id);
        $md = $ds->getMetadata();

        $this->assertNotNull($md);
        $this->assertEquals(1301955963, $md['createdAt']);
        $this->assertEquals("PUBLIC_DOMAIN", $md['licenseId']);
    }

    public function testGetMetadataAsObject ()
    {
        $this->client->disableAssociativeArrays();

        $ds = new SodaDataset($this->client, $this->id);
        $md = $ds->getMetadata();

        $this->assertInstanceOf("stdClass", $md);
        $this->assertNotNull($md->createdAt);
    }

    public function testGetApiVersion ()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $this->assertEquals(1, $ds->getApiVersion());
    }

    public function testGetResource ()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $this->assertTrue(count($ds->getDataset()) > 5);
    }

    public function testGetResourceWithSoqlQuery ()
    {
        $ds   = new SodaDataset($this->client, $this->id);
        $soql = new SoqlQuery();

        $soql->select("date_posted", "state", "sample_type")->where("state = 'AR'");

        $results = $ds->getDataset($soql);
        $this->assertTrue(count($results) > 1);
    }
}
