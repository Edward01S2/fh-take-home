<?php
namespace PokerHand;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PokerHandTest extends TestCase
{
    #[DataProvider('pokerHandProvider')]
    #[Test]
    public function testPokerHandRanking($input, $expectedRank)
    {
        $hand = new PokerHand($input);
        $this->assertEquals($expectedRank, $hand->getRank());
    }

    public static function pokerHandProvider()
    {
        return [
            'Royal Flush' => ['As Ks Qs Js 10s', 'Royal Flush'],
            'Straight Flush' => ['3s 4s 5s 6s 7s', 'Straight Flush'],
            'Four of a Kind' => ['As Ac Ah Ad 2s', 'Four of a Kind'],
            'Full House' => ['9s 9c 9h 2d 2s', 'Full House'],
            'Flush' => ['Kh Qh 4h 2h 9h', 'Flush'],
            'Straight' => ['9s 10h Jc Qd Ks', 'Straight'],
            'Three of a Kind' => ['8s 8c 8h 2d 5s', 'Three of a Kind'],
            'Two Pair' => ['Kh Kc 3s 3h 2d', 'Two Pair'],
            'One Pair' => ['Kh Kc 3s 7h 2d', 'One Pair'],
            'High Card' => ['Ah Qc 6s 3h 2d', 'High Card'],
        ];
    }

    #[Test]
    public function testThrowsExceptionForInvalidInput()
    {   
        $this->expectException(\InvalidArgumentException::class);
        $hand = new PokerHand('As 2h 3c 4d');
    }

    #[Test]
    public function testAceLowStraight()
    {
        $hand = new PokerHand('As 2h 3c 4d 5s');
        $this->assertEquals('Straight', $hand->getRank());
    }

    #[Test]
    public function testStraightWithAceHigh()
    {
        $hand = new PokerHand('10c Jh Qd Ks Ah');
        $this->assertEquals('Straight', $hand->getRank());
    }

    #[Test]
    public function testThrowsExceptionForInvalidCardFormat()
    {   
        $this->expectException(\InvalidArgumentException::class);
        $hand = new PokerHand('X1 2h 3c 4d 5s'); // Invalid card format
    }

    #[Test]
    public function testThrowsExceptionForDuplicateCards()
    {   
        $this->expectException(\InvalidArgumentException::class);
        $hand = new PokerHand('As As 3c 4d 5s'); // Duplicate Ace of spades
    }

    #[Test]
    public function testThrowsExceptionForInvalidSuit()
    {   
        $this->expectException(\InvalidArgumentException::class);
        $hand = new PokerHand('Ax 2h 3c 4d 5s'); // 'x' is not a valid suit
    }
}