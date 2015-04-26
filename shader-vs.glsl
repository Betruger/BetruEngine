 attribute vec3 aVertexPosition;
uniform mat4 uMVMatrix;
uniform mat4 uPMatrix;
uniform mat4 uOMatrix;

	void main(void) {
	vec4 altVertexPosition = vec4(aVertexPosition, 0.0) * uOMatrix;
	altVertexPosition[3] = 1.0;
	gl_Position = uPMatrix * uMVMatrix * altVertexPosition;

	}
