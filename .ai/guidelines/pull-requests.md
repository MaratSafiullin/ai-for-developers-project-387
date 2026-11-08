# Pull Requests

- Fill every PR description from `.github/pull_request_template.md`, with all the links the `Контекст` hint asks for and a `Closes #N` line for each issue the PR completes. Write no `Closes` line for a spec that still has open tickets.
- GitHub pre-fills the template only in the web UI and in interactive `gh pr create`; it does not apply to `gh pr create --body-file` or to the GitHub MCP `create_pull_request`. Open the file, keep its headings and order, and replace each hint comment with content. If a part of a section does not apply, such as before and after for a docs-only change, say so in one line with the reason instead of deleting it.
- The template's structure wins over the `/pr` skill. Use `/pr` guidance for the visuals in `Summary` and the evidence in `Evidence`; in `Merge Danger`, the template's fields replace `/pr`'s `Door` and `Blast Radius`, and reversibility goes to `Откат`.
