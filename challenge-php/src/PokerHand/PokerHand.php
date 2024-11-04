<?php

namespace PokerHand;

class PokerHand
{
  private $hand;

  public function __construct($hand)
  {
    $this->hand = $this->parseHand($hand);
  }

  public function getRank(): string
  {
    return match(true) {
        $this->isRoyalFlush() => 'Royal Flush',
        $this->isStraightFlush() => 'Straight Flush',
        $this->isFourOfAKind() => 'Four of a Kind',
        $this->isFullHouse() => 'Full House',
        $this->isFlush() => 'Flush',
        $this->isStraight() => 'Straight',
        $this->isThreeOfAKind() => 'Three of a Kind',
        $this->isTwoPair() => 'Two Pair',
        $this->isOnePair() => 'One Pair',
        default => 'High Card'
    };
  }

  private function parseHand($hand)
  {
    $cards = explode(' ', $hand);

    // Check if we have exactly 5 cards
    if (count($cards) !== 5) {
      throw new \InvalidArgumentException('Hand must contain exactly 5 cards');
    }

    $parsed = [];
    foreach ($cards as $card) {
      // Check valid card format
      if (!preg_match('/^(10|[2-9]|[JQKA])[shdc]$/', $card)) {
        throw new \InvalidArgumentException('Invalid card format: ' . $card);
      }
      
      $rank = substr($card, 0, -1);
      $suit = substr($card, -1);
      
      try {
        $parsed[] = [
          'rank' => $this->rankToValue($rank),
          'suit' => $suit,
        ];
      } catch (\Exception $e) {
        throw new \InvalidArgumentException('Invalid card rank: ' . $rank);
      }
    }
    
    // Check for duplicate cards
    $cardStrings = array_map(function($card) {
      return $card['rank'] . $card['suit'];
    }, $parsed);
    
    if (count($cardStrings) !== count(array_unique($cardStrings))) {
      throw new \InvalidArgumentException('Duplicate cards are not allowed');
    }
    
    return $parsed;
  }

  private function rankToValue($rank) {
    $values = [
        '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        '10' => 10, 'J' => 11, 'Q' => 12, 'K' => 13, 'A' => 14
    ];
    return $values[$rank];
  }

  private function isRoyalFlush() {
      return $this->isFlush() && $this->isStraight() && max(array_column($this->hand, 'rank')) == 14;
  }

  private function isStraightFlush() {
      return $this->isFlush() && $this->isStraight();
  }

  private function isFourOfAKind() {
      return $this->hasOfAKind(4);
  }

  private function isFullHouse() {
      $counts = $this->rankCounts();
      return in_array(3, $counts) && in_array(2, $counts);
  }

  private function isFlush() {
    $suits = array_column($this->hand, 'suit');
    return count(array_unique($suits)) == 1;
  }

  private function isStraight() {
    $ranks = array_column($this->hand, 'rank');
    sort($ranks);
    
    // Check normal straight
    $isNormalStraight = true;
    for ($i = 1; $i < count($ranks); $i++) {
        if ($ranks[$i] - $ranks[$i - 1] != 1) {
            $isNormalStraight = false;
            break;
        }
    }
    
    // Check Ace-low straight (A-2-3-4-5)
    $isAceLowStraight = false;
    if (in_array(14, $ranks)) { // Has Ace
        $aceLowRanks = array_map(function($rank) {
            return $rank == 14 ? 1 : $rank;
        }, $ranks);
        sort($aceLowRanks);
        
        $isAceLowStraight = true;
        for ($i = 1; $i < count($aceLowRanks); $i++) {
            if ($aceLowRanks[$i] - $aceLowRanks[$i - 1] != 1) {
                $isAceLowStraight = false;
                break;
            }
        }
    }
    
    return ($isNormalStraight || $isAceLowStraight) && count(array_unique($ranks)) == 5;
  }

  private function isThreeOfAKind() {
    return $this->hasOfAKind(3);
  }

  private function isTwoPair() {
    $counts = $this->rankCounts();
    return count(array_filter($counts, fn($count) => $count == 2)) == 2;
  }

  private function isOnePair() {
    return $this->hasOfAKind(2);
  }

  private function hasOfAKind($number) {
    return in_array($number, $this->rankCounts());
  }

  private function rankCounts() {
    $counts = [];
    foreach ($this->hand as $card) {
        $rank = $card['rank'];
        if (!isset($counts[$rank])) $counts[$rank] = 0;
        $counts[$rank]++;
    }
    return array_values($counts);
  }
}