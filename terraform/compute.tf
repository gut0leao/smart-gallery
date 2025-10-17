# VM Instance Configuration
# Startup script for basic server preparation
data "template_file" "startup_script" {
  template = file("${path.module}/scripts/startup.sh")
  
  vars = {
    domain_name = var.domain_name
    environment = var.environment
  }
}

# VM Instance
resource "google_compute_instance" "smart_gallery_vm" {
  name         = "${local.name_prefix}-vm"
  machine_type = var.machine_type
  zone         = var.zone
  
  labels = local.labels

  boot_disk {
    initialize_params {
      image = "ubuntu-os-cloud/ubuntu-2204-lts"
      size  = 20 # GB - Free tier allows up to 30GB
      type  = "pd-standard"
    }
  }

  network_interface {
    network    = google_compute_network.smart_gallery_network.id
    subnetwork = google_compute_subnetwork.smart_gallery_subnet.id
    
    access_config {
      nat_ip = google_compute_address.smart_gallery_ip.address
    }
  }

  # Service account for VM operations
  service_account {
    scopes = [
      "https://www.googleapis.com/auth/cloud-platform"
    ]
  }

  tags = ["smart-gallery-server"]

  metadata = {
    ssh-keys               = var.ssh_public_key != "" ? "ubuntu:${var.ssh_public_key}" : null
    enable-oslogin         = "FALSE"
    startup-script         = data.template_file.startup_script.rendered
    block-project-ssh-keys = "TRUE"
  }

  # Allow stopping for maintenance
  allow_stopping_for_update = true

  lifecycle {
    # Prevent destruction of the VM unless explicitly forced
    prevent_destroy = false
    
    # Don't recreate the VM if these attributes change
    ignore_changes = [
      metadata["startup-script"],
      metadata["user-data"],
      boot_disk[0].initialize_params[0].labels,
      labels,
      tags
    ]
    
    # Update in place when possible - avoid recreation
    create_before_destroy = false
    
    # Additional safeguards
    replace_triggered_by = []
  }
  
  timeouts {
    create = "10m"
    update = "10m"
    delete = "10m"
  }
}