FROM debian:trixie-slim AS download

ARG TOOL_VERSION=0.51.0
ARG TARGETARCH

ADD https://github.com/docker/sbx-releases/releases/download/v${TOOL_VERSION}/DockerSandboxes-linux-${TARGETARCH}.tar.gz /tmp/sbx.tar.gz
RUN tar -xzf /tmp/sbx.tar.gz -C /tmp docker-sbx/sbx

FROM scratch AS skills

ARG SKILLS_REVISION
ADD https://github.com/docker/skills.git#${SKILLS_REVISION} /upstream

FROM scratch

COPY --from=download --chmod=0755 /tmp/docker-sbx/sbx /usr/local/bin/sbx
COPY --from=skills /upstream/skills/ /usr/share/infection-sbx/skills/
