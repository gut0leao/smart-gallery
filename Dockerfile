# Demo Dockerfile for Smart Gallery
# Builds a simple PHP + Apache image with WP-CLI to run the demo

FROM wordpress:6.4-apache

# Install system deps and WP-CLI
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    less \
    git \
    curl \
    ca-certificates \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Install WP-CLI
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# Copy plugin source into plugins directory
COPY wp-content/plugins/smart-gallery /var/www/html/wp-content/plugins/smart-gallery

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port and use the official entrypoint
EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
