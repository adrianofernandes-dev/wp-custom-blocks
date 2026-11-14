# AGENTS.md

Rules for any AI coding agent working in this repository.

1. `docs/SPEC.md` is the source of truth. Read it before any change. If a task conflicts with the spec, stop and ask. Do not improvise.
2. Work on one block or one concern per task, following the build order in SPEC §9.
3. PHP follows WordPress Coding Standards. Run `composer phpcs` before finishing a task.
4. Escape late, sanitize early. No unescaped output, ever.
5. Do not add dependencies, front-end JavaScript or new blocks outside the spec.
6. Do not hand-edit `build/`. Run `npm run build`.
7. When a task is done, list which acceptance criteria from SPEC §5 it satisfies.
