# Review Criteria

Criteria for the automatic pull request review (`.github/workflows/auto-review.yml`). The workflow pastes this file from the base branch into the reviewer's prompt. The domain language is in `GLOSSARY.md`, the decisions are in `docs/adr/`: read them there, this file does not restate them.

## Calendar invariants

A change that breaks one of these is a defect, whatever the spec says:

- A busy interval is never a slot and cannot be booked again, even by another meeting type or by concurrent requests. Meetings never overlap, even partly, but may touch: one may start exactly when another ends. See `GLOSSARY.md` (**Слот**, **Встреча**) and `docs/adr/0002-meeting-cells-unique-index.md`.
- Once meetings can be cancelled, cancelling a meeting frees exactly its own interval and leaves touching meetings untouched.
- The booking window and the working hours are computed in the owner timezone from `config/owner.php`, not in UTC or the server timezone. The database stores UTC.
- The owner API (`api/owner/v1`) and the guest API (`api/guest/v1`) do not share rights: a guest route never exposes owner data or owner actions.

## Scope

- Compare the diff with the spec, the tickets and the approval linked in the description. Flag changes outside the agreed scope and acceptance criteria the diff does not meet.
- If the description has no links, or a link does not open, say so: the review then lacks context.

## Depth

Choose how deep to check by the consequence of an error, not by the number of changed lines. A button label needs a glance; slot computation, booking conflicts and workflow permissions need the edge cases traced through the code.

## Findings

- Report only defects you can back with the code, the spec or the CI results; leave out style preferences and guesses. Each finding gives:
    - the file and the line;
    - the condition under which the error happens;
    - the consequence for the user, the data or the repository;
    - your confidence: confirmed or plausible.
- Do not repeat what the linter, the formatter, the type checkers and the tests already enforce in `composer ci:check`.
- If you found no confirmed defects, say plainly: «Подтверждённых дефектов не найдено». Do not invent findings to fill the review.

## Earlier reviews

Read the earlier comments on the pull request, including previous automatic reviews and replies to them. Do not raise again a finding that was fixed or that a maintainer rejected with a reason. Check the new commits since the last review, and say which earlier findings are now fixed.
