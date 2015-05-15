 attribute vec3 position;
uniform mat4 Pmatrix, Lmatrix, Mmatrix;
//uniform mat4 Pmatrix;

varying float vDepth;

void main(void) {
vec4 position = Pmatrix*Lmatrix*Mmatrix*vec4(position, 1.0);
//vec4 position = Pmatrix*vec4(position, 1.0);

float zBuf=position.z/position.w;
vDepth=0.5+zBuf*0.5;
gl_Position=position;
}