FROM debian:bookworm-slim AS build-env

RUN apt-get update \
  && apt-get install -y --no-install-recommends zip git curl \
  && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://github.com/cli/cli/releases/download/v2.50.0/gh_2.50.0_linux_amd64.tar.gz \
  | tar -xz -C /usr/local --strip-components=1 gh_2.50.0_linux_amd64/bin/gh

FROM build-env AS docs-env

RUN apt-get update \
  && apt-get install -y --no-install-recommends python3 python3-pip python3-venv \
  && rm -rf /var/lib/apt/lists/*

ENV VIRTUAL_ENV=/opt/venv
RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"
