<?php

namespace Vinelab\NeoEloquent\Tests\Functional\AttributeRelations;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\SoftDeletes;

class AttributeRelationsTest extends TestCase
{
    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function testWhereEdgeIdWithNoOperator()
    {
        $this->markTestSkipped('not yet implemented');
        $alice = new User(['name' => 'Alice']);
        $alice->save();
        $club = new Organization(['name' => 'NeoEloquent Devs']);
        $alice->orgs()->save($club);

        $edge = $alice->orgs()->edge($club);

        $edgeIdQuery = $club->members()->whereRel('id', $edge->id);
        $edgeLoaded = $edgeIdQuery->get();

//        $this->assertEquals($this->ab->toArray(), $u->toArray());
    }

    public function testSimpleEdgeAttributeQuery()
    {
        $this->markTestSkipped('not yet implemented');
        $alice = new User(['name' => 'Alice']);
        $alice->save();
        $bob = new User(['name' => 'Bob']);
        $bob->save();

        $club = new Organization(['name' => 'NeoEloquent Devs']);

        $alice->orgs()->save($club);
        $bob->orgs()->save($club);

        $userLoaded = $club->members()->get();
        $this->assertEquals(2, count($userLoaded));

        $membershipRelationAlice = $club->members()->edge($alice);
        $membershipRelationAlice->status = 'active';
        $membershipRelationAlice->save();

        $membershipRelationBob = $club->members()->edge($bob);
        $membershipRelationBob->status = 'expired';
        $membershipRelationBob->save();

        $activeMembersQuery = $club->members()->whereRel('status', '=', 'active');
        $activeMembers = $activeMembersQuery->get();

        $this->assertEquals(1, count($activeMembers));
    }

    public function testEdgeAttributeQueryBinaryOps()
    {
        $this->markTestSkipped('not yet implemented');
        $organization = Organization::create(['name' => 'NeoEloquent Devs']);
        for ($i = 0; $i < 4; ++$i) {
            $user = User::create(['name' => 'user'.$i]);
            $organization->members()->save($user);
            $membershipRelation = $organization->members()->edge($user);
            if ($i < 2) {
                $membershipRelation->status = 'active';
            } else {
                $membershipRelation->status = 'expired';
            }
            $membershipRelation->save();
        }
        $activeMembers = $organization->members()->whereRel('status', '=', 'active')->get();
        $this->assertEquals(2, count($activeMembers));
        foreach ($activeMembers as $member) {
            $membershipEdge = $organization->members()->edge($member);
            $this->assertEquals('active', $membershipEdge->status);
        }
    }

    public function testEdgeAttributeQueryWithNullValue()
    {
        $this->markTestSkipped('not yet implemented');
        $organization = Organization::create(['name' => 'cOrg']);
        for ($i = 0; $i < 4; ++$i) {
            $user = User::create(['name' => 'user'.$i]);
            $organization->members()->save($user);
            $membershipRelation = $organization->members()->edge($user);
            if ($i < 2) {
                $membershipRelation->status = 'active';
            }
            $membershipRelation->save();
        }
        $membersWithoutStatus = $organization->members()->whereRel('status', '=', null)->get();
        $this->assertEquals(2, count($membersWithoutStatus));
        foreach ($membersWithoutStatus as $member) {
            $membershipEdge = $organization->members()->edge($member);
            $this->assertNull($membershipEdge->status);
        }
        $membersWithStatus = $organization->members()->whereRel('status', 'IS NOT NULL')->get();
        $this->assertEquals(2, count($membersWithStatus));
        foreach ($membersWithStatus as $member) {
            $membershipEdge = $organization->members()->edge($member);
            $this->assertNotNull($membershipEdge->status);
        }
    }
}

class User extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $label = 'User';
    protected $fillable = ['name', 'dob'];

    public function orgs()
    {
        return $this->belongsToMany('Vinelab\NeoEloquent\Tests\Functional\AttributeRelations\Organization', 'MEMBER_OF');
    }
}

class Organization extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $label = 'Organization';
    protected $fillable = ['name'];

    public function members()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\AttributeRelations\User', 'MEMBER_OF');
    }
}
