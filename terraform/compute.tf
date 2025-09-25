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
    ssh-keys               = "ubuntu:${file("~/.ssh/id_rsa.pub")}"
    enable-oslogin         = "TRUE"
    startup-script         = data.template_file.startup_script.rendered
    block-project-ssh-keys = "TRUE"
  }

  # Allow stopping for maintenance
  allow_stopping_for_update = true

  lifecycle {
    create_before_destroy = true
  }
  
  timeouts {
    create = "10m"
    delete = "10m"
  }
}