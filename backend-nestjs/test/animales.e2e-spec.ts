
import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication, ValidationPipe } from '@nestjs/common';
import request from 'supertest';
import { AppModule } from './../src/app.module';

describe('AnimalesController (E2E)', () => {
  let app: INestApplication;
  let createdAnimalId: number; // Guardará el ID del animal creado dinámicamente

  beforeAll(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();

    app = moduleFixture.createNestApplication();

    app.setGlobalPrefix('api');
    app.useGlobalPipes(
      new ValidationPipe({
        whitelist: true,
        forbidNonWhitelisted: true,
        transform: true,
        errorHttpStatusCode: 422,
      }),
    );

    await app.init();
  });

  afterAll(async () => {
    await app.close();
  });

  it('Debe estar definido el entorno de prueba', () => {
    expect(app).toBeDefined();
  });

  it('POST /api/v1/animales -> Debe crear un animal exitosamente (201)', async () => {
    const nuevoAnimal = {
      numero_arete: `ART-TEST-${Date.now()}`, // Arete único en cada ejecución
      raza_id: 1,
      sexo: 'Macho',
      fecha_nacimiento: '2024-01-10',
      estado: 'Activo',
      potrero_id: 1,
    };

    const response = await request(app.getHttpServer())
      .post('/api/v1/animales')
      .send(nuevoAnimal)
      .expect(201);

    expect(response.body).toHaveProperty('id_animal');
    expect(response.body.sexo).toBe('Macho');

    // Capturamos el ID para usarlo en el DELETE
    createdAnimalId = response.body.id_animal;
  });

  it('POST /api/v1/animales -> Debe retornar 422 si faltan datos o son inválidos', async () => {
    const animalInvalido = {
      raza_id: 'no-es-numero',
      sexo: 'Otro',
      potrero_id: 1,
    };

    const response = await request(app.getHttpServer())
      .post('/api/v1/animales')
      .send(animalInvalido)
      .expect(422);

    expect(response.body).toHaveProperty('message');
    expect(Array.isArray(response.body.message)).toBe(true);
  });

  it('GET /api/v1/animales -> Debe obtener la lista de animales (200)', async () => {
    const response = await request(app.getHttpServer())
      .get('/api/v1/animales')
      .expect(200);

    expect(Array.isArray(response.body)).toBe(true);
  });

  it('GET /api/v1/potreros/:potreroId/animales -> Debe listar animales por potrero (200)', async () => {
    const response = await request(app.getHttpServer())
      .get('/api/v1/potreros/1/animales')
      .expect(200);

    expect(Array.isArray(response.body)).toBe(true);
  });

  it('DELETE /api/v1/animales/:id -> Debe eliminar un animal existente (204)', async () => {
    // Eliminamos usando el ID capturado
    await request(app.getHttpServer())
      .delete(`/api/v1/animales/${createdAnimalId}`)
      .expect(204);
  });
});