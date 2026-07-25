<?php

use App\Http\Controllers\Api\VotingRound\VotingRoundController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->prefix('voting-rounds')->group(function () {
    // Create / List / Update rounds
    Route::get('/', [VotingRoundController::class, 'index']);
    Route::post('/', [VotingRoundController::class, 'store']);
    Route::get('{votingRound}', [VotingRoundController::class, 'show']);
    Route::put('{votingRound}', [VotingRoundController::class, 'update']);

    // Candidate management within a round
    Route::get('{votingRound}/candidates', [VotingRoundController::class, 'candidates']);
    Route::post('{votingRound}/candidates', [VotingRoundController::class, 'addCandidates']);

    // Voting
    Route::post('{votingRound}/candidates/{cid}/vote', [VotingRoundController::class, 'castVote']);

    // My votes
    Route::get('{votingRound}/my-votes', [VotingRoundController::class, 'myVotes']);

    // Tally & Results
    Route::get('{votingRound}/tally', [VotingRoundController::class, 'tally']);
    Route::post('{votingRound}/lock', [VotingRoundController::class, 'lock']);
    Route::get('{votingRound}/results', [VotingRoundController::class, 'results']);
});
