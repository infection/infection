FROM scratch

ARG TOOL_VERSION=1.22.1
ARG TARGETARCH

ADD --chmod=0755 https://github.com/GoogleContainerTools/container-structure-test/releases/download/v${TOOL_VERSION}/container-structure-test-linux-${TARGETARCH} /usr/local/bin/container-structure-test
