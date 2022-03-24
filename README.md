Hello, thanks for checking out this repo!

## About Read Right Hands

Read Right Hands is a basic poker game under development using Laravel and Vue.js 2.

It's a project I've been working on to practice some new knowledge and workflow approaches I've learned recently. Mainly test driven development and writing code in a more OOP way.

I'm keen to share the repository publicly and get some feedback from other developers, which is something I have not had much of over the years!

I'm particularly interested in hearing from any poker playing devs out there.

### What The Game Can Do At Present

There is currently only one six-seater table where 1 game can take place at a time. However, the codebase was partly written with the possibility of accommodating multiple tables/games in the future.

A new hand will be started each time the page is refreshed of when you click the 'next hand' button after the current hand is complete.

Here is a list of some technical aspects of the project:

- Card, Rank, and Suit models that compile into a 52 card deck during the build process
- A dealer class that can shuffle the deck and deal cards to players and hand streets
  - The amount of whole cards and street cards dealt will depend on the game type being played (Currently only PotLimitHoldEm exists as an implementation of the Game interface)
- A GamePlay class that is the primary handler of the game that figures out the following based on the latest PlayerAction:
  - What the next step in the hand is
  - What the status of the last player to act should be
  - What the status of the other players still in the hand should be
- A HandIdentifier class that reads the community cards whole cards of each player that is still in the hand after the river and identifies what hands are in play as well as the rankings and kickers if appropriate
- A Showdown class that decides the winner based on the data from the HandIdentifier

### What The Game Can NOT Do At Present

At the moment, you control every action of each player and can see all the cards in play. The next steps planned here are:

- Set players 2-6 as AI
- Hide the AI cards from the front-end user (Player 1)
- Programme a basic randomised response for each AI player

This would at least produce a slightly more engaging game.

### Future Changes

- Handle split pots
- End the game when 1 player has all the chips in play
- Add user accounts
- Store hand history including every individual action of the players

### Build Process

- Run: php artisan build:env
  - This will populate the DB with all the required resources (Table, Players, Cards etc)

