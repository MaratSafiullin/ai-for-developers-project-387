# Commit Messages

- Write commit messages following [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/): `<type>[optional scope]: <description>`, for example `feat: добавить форму бронирования` or `fix(api): вернуть 404 для неизвестного слота`.
- Use only these types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.
- Write the type and scope in English, and the description and body in Russian. Put the description in the infinitive, starting with a lowercase Cyrillic letter or a digit, without a trailing period. A PR title follows the same format.
- An implementation commit references the ticket it closes: append the GitHub issue number in parentheses at the end of the description, for example `feat: запись на свободный слот (#12)`. GitHub turns the number into a link, so the history shows which commit did which ticket. Commits not tied to a ticket, such as release-please's `chore(main): release …` or merge commits, go without a number. The hook does not enforce this rule.
- The `.githooks/commit-msg` hook enforces this format and keeps its own copy of the type list; update both together. If the hook rejects a message, fix the message. Never bypass the hook with `--no-verify`.
- Intermediate commits made at the user's request follow the same rules: pick the type by what the change does, never `wip:` or `tmp:`.
