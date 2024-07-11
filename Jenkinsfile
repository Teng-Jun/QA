pipeline {
    agent any
    stages {
        stage('Build') {
            steps {
                withCredentials([string(credentialsId: '1234567890', variable: 'GIT_TOKEN')]){
                    sh '''
                    #!/bin/bash

                    # Start the SSH agent and add the key
                    eval $(ssh-agent -s)
                    ssh-add /root/.ssh/id_rsa

                    # Navigate to the project directory
                    cd "/var/jenkins_home/workspace/pixiegram_pipeline"

                    # Set up GitHub authentication
                    GIT_REPO="https://github.com/Kingstonx3/Pixiegram"
                    GIT_URL="https://${GIT_TOKEN}@github.com/Kingstonx3/Pixiegram.git"

                    # Pull the latest changes
                    git pull $GIT_URL main
                    ''' 
                } 
            }
        }
        stage('Test') {
            steps {
                sh '''
                # Define the target directory on the VM
                TARGET_DIR="/var/www/pixiegram"
                VM_USER="student10"
                VM_HOST="10.2.1.87"
   
                # Copy files to the VM
                scp -r . ${VM_USER}@${VM_HOST}:${TARGET_DIR}

                # Run commands on the remote VM to set permissions
                ssh ${VM_USER}@${VM_HOST} << EOF
                sudo chown -R ${VM_USER}:${VM_USER} ${TARGET_DIR}
                sudo chmod -R 755 ${TARGET_DIR}

                # Run PHPUnit tests on the remote VM
                cd ${TARGET_DIR}
                php artisan test --log-junit ${TARGET_DIR}/test-report.xml
                '''
            }
        }
        
        stage('OWASP DependencyCheck') {
			steps {
				dependencyCheck additionalArguments: '--format HTML --format XML --noupdate', odcInstallation: 'OWASP Dependency-Check Vulnerabilities'
			}
		}
    }
    post {
		success {
            // Publish Dependency-Check report
			dependencyCheckPublisher pattern: 'dependency-check-report.xml'
		}
	}
}
