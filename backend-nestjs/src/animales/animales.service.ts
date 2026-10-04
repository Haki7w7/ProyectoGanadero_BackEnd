
import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Animal } from './entities/animal.entity';
import { CreateAnimalDto } from './dto/create-animal.dto';
import { UpdateAnimalDto } from './dto/update-animal.dto';

@Injectable()
export class AnimalesService {
  constructor(
    @InjectRepository(Animal)
    private readonly animalRepository: Repository<Animal>,
  ) {}

  async create(createAnimalDto: CreateAnimalDto): Promise<Animal> {
    const nuevoAnimal = this.animalRepository.create(createAnimalDto);
    return await this.animalRepository.save(nuevoAnimal);
  }

  async findAll(query?: any): Promise<Animal[]> {
    const where: any = {};
    if (query?.raza_id) where.raza_id = query.raza_id;
    if (query?.potrero_id) where.potrero_id = query.potrero_id;
    if (query?.sexo) where.sexo = query.sexo;
    if (query?.estado) where.estado = query.estado;

    return await this.animalRepository.find({ where });
  }

  async findOne(id: number): Promise<Animal> {
    const animal = await this.animalRepository.findOneBy({ id_animal: id });
    if (!animal) {
      throw new NotFoundException(`El animal con ID ${id} no fue encontrado`);
    }
    return animal;
  }

  async update(id: number, updateAnimalDto: UpdateAnimalDto): Promise<Animal> {
    const animal = await this.findOne(id);
    this.animalRepository.merge(animal, updateAnimalDto);
    return await this.animalRepository.save(animal);
  }

  async remove(id: number): Promise<void> {
    const animal = await this.findOne(id);
    await this.animalRepository.remove(animal);
  }

  async findByPotrero(potrero_id: number): Promise<Animal[]> {
    return await this.animalRepository.findBy({ potrero_id });
  }

  async findByRaza(raza_id: number): Promise<Animal[]> {
    return await this.animalRepository.findBy({ raza_id });
  }
}