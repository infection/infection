FROM scratch AS source

ARG GH_STACK_REVISION
ADD https://github.com/github/gh-stack.git#${GH_STACK_REVISION} /upstream

FROM scratch

COPY --from=source /upstream/skills/gh-stack/ /usr/share/infection-sbx/skills/gh-stack/
