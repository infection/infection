# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

.DEFAULT_GOAL := replace_absolute_paths

.PHONY: replace_absolute_paths
replace_absolute_paths:
	for file in *.xml; do \
		sed -i '' -E 's|"/[^"]*/infection/|"/path/to/infection/|g' "$$file"; \
		sed -i '' -E 's|"/[^"]*/codeception-adapter/|"/path/to/codeception-adapter/|g' "$$file"; \
	done
